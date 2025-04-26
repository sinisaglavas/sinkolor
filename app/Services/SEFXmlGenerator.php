<?php


namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SEFXmlGenerator
{

    public function generate(CustomerInvoice $invoice): string
    {
        $customer = $invoice->customer;
        $company = config('sef');

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Invoice xmlns="urn:cen.eu:en16931:2017" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></Invoice>');

        // Obavezna zaglavlja
        $xml->addChild('CustomizationID', 'urn:cen.eu:en16931:2017');
        $xml->addChild('ProfileID', 'urn:fdc:gov.rs:2022:sef:invoice:01');
        $xml->addChild('ID', $invoice->invoice_number);
        $xml->addChild('IssueDate', $invoice->invoicing_date);
        $xml->addChild('InvoiceTypeCode', '380'); // 380 = Faktura
        $xml->addChild('DocumentCurrencyCode', 'RSD');

        // Dobavljač (prodavac)
        $supplier = $xml->addChild('AccountingSupplierParty');
        $supplierParty = $supplier->addChild('Party');
        $supplierParty->addChild('EndpointID', $company['pib']);
        $supplierParty->addChild('PartyName')->addChild('Name', $company['company_name']);

        $supplierAddress = $supplierParty->addChild('PostalAddress');
        $supplierAddress->addChild('StreetName', $company['address']);
        $supplierAddress->addChild('CityName', $company['city']);
        $supplierAddress->addChild('PostalZone', $company['postal_code']);
        $supplierAddress->addChild('Country')->addChild('IdentificationCode', 'RS');

        $supplierParty->addChild('PartyTaxScheme')->addChild('CompanyID', $company['pib']);
        $supplierParty->PartyTaxScheme->addChild('TaxScheme')->addChild('ID', 'VAT');

        $supplierParty->addChild('PartyLegalEntity')->addChild('CompanyID', $company['mb']);

        // Kupac
        $customerXml = $xml->addChild('AccountingCustomerParty');
        $customerParty = $customerXml->addChild('Party');
        $customerParty->addChild('EndpointID', $customer->pib);
        $customerParty->addChild('PartyName')->addChild('Name', $customer->customer);

        $customerAddress = $customerParty->addChild('PostalAddress');
        $customerAddress->addChild('StreetName', $customer->address);
        $customerAddress->addChild('CityName', $customer->city);
        $customerAddress->addChild('PostalZone', 'RS');
        $customerAddress->addChild('Country')->addChild('IdentificationCode', 'RS');

        $customerParty->addChild('PartyTaxScheme')->addChild('CompanyID', $customer->pib);
        $customerParty->PartyTaxScheme->addChild('TaxScheme')->addChild('ID', 'VAT');

        $customerParty->addChild('PartyLegalEntity')->addChild('CompanyID', $customer->mb);

        // Stavke i PDV
        $lineId = 1;
        $totalWithoutTax = 0;
        $totalTax = 0;
        $taxRate = 0; // ako nema PDV, ostaje 0

        foreach ($invoice->outputs as $item) {
            $line = $xml->addChild('InvoiceLine');
            $line->addChild('ID', $lineId++);
            $line->addChild('InvoicedQuantity', $item->pcs)->addAttribute('unitCode', 'C62');
            $lineTotal = $item->price * $item->pcs;
            $line->addChild('LineExtensionAmount', number_format($lineTotal, 2, '.', ''))->addAttribute('currencyID', 'RSD');

            // PDV za stavku
            $taxCategory = $line->addChild('Item')->addChild('ClassifiedTaxCategory');
            $taxCategory->addChild('ID', 'S');
            $taxCategory->addChild('Percent', $taxRate);
            $taxCategory->addChild('TaxScheme')->addChild('ID', 'VAT');

            $line->Item->addChild('Name', $item->article);

            $priceNode = $line->addChild('Price');
            $priceNode->addChild('PriceAmount', number_format($item->price, 2, '.', ''))->addAttribute('currencyID', 'RSD');

            $totalWithoutTax += $lineTotal;
        }

        // TaxTotal
        $taxTotal = $xml->addChild('TaxTotal');
        $taxTotal->addChild('TaxAmount', number_format($totalTax, 2, '.', ''))->addAttribute('currencyID', 'RSD');

        $taxSubtotal = $taxTotal->addChild('TaxSubtotal');
        $taxSubtotal->addChild('TaxableAmount', number_format($totalWithoutTax, 2, '.', ''))->addAttribute('currencyID', 'RSD');
        $taxSubtotal->addChild('TaxAmount', number_format($totalTax, 2, '.', ''))->addAttribute('currencyID', 'RSD');
        $taxCategory = $taxSubtotal->addChild('TaxCategory');
        $taxCategory->addChild('ID', 'S');
        $taxCategory->addChild('Percent', $taxRate);
        $taxCategory->addChild('TaxScheme')->addChild('ID', 'VAT');

        // PaymentMeans
        $paymentMeans = $xml->addChild('PaymentMeans');
        $paymentMeans->addChild('PaymentMeansCode', '30'); // 30 = bank transfer
        $paymentMeans->addChild('PaymentDueDate', $invoice->invoicing_date); // isti dan, možeš staviti +7 dana

        // PaymentTerms
        $paymentTerms = $xml->addChild('PaymentTerms');
        $paymentTerms->addChild('Note', 'Plaćanje po prijemu fakture');

        // Ukupni iznosi
        $legalMonetaryTotal = $xml->addChild('LegalMonetaryTotal');
        $legalMonetaryTotal->addChild('LineExtensionAmount', number_format($totalWithoutTax, 2, '.', ''))->addAttribute('currencyID', 'RSD');
        $legalMonetaryTotal->addChild('TaxExclusiveAmount', number_format($totalWithoutTax, 2, '.', ''))->addAttribute('currencyID', 'RSD');
        $legalMonetaryTotal->addChild('TaxInclusiveAmount', number_format($totalWithoutTax + $totalTax, 2, '.', ''))->addAttribute('currencyID', 'RSD');
        $legalMonetaryTotal->addChild('PayableAmount', number_format($totalWithoutTax + $totalTax, 2, '.', ''))->addAttribute('currencyID', 'RSD');

        // Dodatak: PDF faktura
        $pdfPath = public_path("invoices/{$invoice->invoice_number}.pdf");
        if (file_exists($pdfPath)) {
            $pdfContent = base64_encode(file_get_contents($pdfPath));
            $attachment = $xml->addChild('AdditionalDocumentReference');
            $attachment->addChild('ID', 'PDF');
            $attachment->addChild('DocumentType', 'SupportingDocument');
            $binary = $attachment->addChild('Attachment')->addChild('EmbeddedDocumentBinaryObject', $pdfContent);
            $binary->addAttribute('mimeCode', 'application/pdf');
        }

        return $xml->asXML();
    }



}

