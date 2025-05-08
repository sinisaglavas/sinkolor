<?php


namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SEFXmlGenerator
{
    public function generate(CustomerInvoice $invoice): array //  vraća i XML i DocumentId zajedno — npr. kao asocijativni niz (array), umesto samo stringa
    {
        $company = config('sef');
        $customer = $invoice->customer;
        $items = $invoice->outputs;

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        // === Envelope ===
        $envelope = $doc->createElementNS('urn:eFaktura:MinFinrs:envelop:schema', 'env:DocumentEnvelope');
        $doc->appendChild($envelope);

        // === Header ===
        $header = $doc->createElement('env:DocumentHeader');
        $envelope->appendChild($header);

        // SalesInvoiceId i PurchaseInvoiceId SE NE DODAJU jer ih dodeljuje SEF
        $documentId = (string) \Str::uuid();
        $header->appendChild($doc->createElement('env:DocumentId', $documentId));
        $header->appendChild($doc->createElement('env:CreationDate', now()->format('Y-m-d')));
        $header->appendChild($doc->createElement('env:SendingDate', now()->format('Y-m-d')));


        // === DocumentBody ===
        $body = $doc->createElement('env:DocumentBody');
        $envelope->appendChild($body);

        // === Invoice sa namespace-ovima ===
        $invoiceNode = $doc->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cec', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sbt', 'http://mfin.gov.rs/srbdt/srbdtext');
        $body->appendChild($invoiceNode);

        // === Obavezna polja ===
        $invoiceNode->appendChild($doc->createElement('cbc:CustomizationID', 'urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.rs:srbdt:2022'));
        $invoiceNode->appendChild($doc->createElement('cbc:ProfileID', 'urn:mfin.gov.rs:srbdt:01:2022'));
        $invoiceNode->appendChild($doc->createElement('cbc:ID', $invoice->invoice_number));
        $invoiceNode->appendChild($doc->createElement('cbc:IssueDate', $invoice->invoicing_date));
        $invoiceNode->appendChild($doc->createElement('cbc:DueDate', $invoice->invoicing_date));
        $invoiceNode->appendChild($doc->createElement('cbc:InvoiceTypeCode', '380'));
        $invoiceNode->appendChild($doc->createElement('cbc:DocumentCurrencyCode', 'RSD'));

        // === Supplier (dobavljač) ===
        $supplier = $doc->createElement('cac:AccountingSupplierParty');
        $party = $doc->createElement('cac:Party');
        $supplier->appendChild($party);

        $endpoint = $doc->createElement('cbc:EndpointID', $company['pib']);
        $endpoint->setAttribute('schemeID', '9948');
        $party->appendChild($endpoint);
        $partyName = $doc->createElement('cac:PartyName');
        $partyName->appendChild($doc->createElement('cbc:Name', $company['company_name']));
        $party->appendChild($partyName);
        $address = $doc->createElement('cac:PostalAddress');
        $address->appendChild($doc->createElement('cbc:StreetName', $company['address']));
        $address->appendChild($doc->createElement('cbc:CityName', $company['city']));
        $address->appendChild($doc->createElement('cbc:PostalZone', $company['postal_code']));
        $country = $doc->createElement('cac:Country');
        $country->appendChild($doc->createElement('cbc:IdentificationCode', 'RS'));
        $address->appendChild($country);
        $party->appendChild($address);
        $taxScheme = $doc->createElement('cac:PartyTaxScheme');
        $taxScheme->appendChild($doc->createElement('cbc:CompanyID', 'RS' . $company['pib']));
        $party->appendChild($taxScheme);
        $legalEntity = $doc->createElement('cac:PartyLegalEntity');
        $legalEntity->appendChild($doc->createElement('cbc:CompanyID', $company['mb']));
        $party->appendChild($legalEntity);
        $invoiceNode->appendChild($supplier);

        // === Customer (kupac) ===
        $customerNode = $doc->createElement('cac:AccountingCustomerParty');
        $custParty = $doc->createElement('cac:Party');
        $customerNode->appendChild($custParty);

        // EndpointID (PIB)
        $cEnd = $doc->createElement('cbc:EndpointID', $customer->pib);
        $cEnd->setAttribute('schemeID', '9948');
        $custParty->appendChild($cEnd);

        // Dodavanje JBKJS ako postoji
        if (!empty($customer->jbkjs)) {
            $cPartyIdent = $doc->createElement('cac:PartyIdentification');
            $cPartyIdent->appendChild(
                $doc->createElement('cbc:ID', 'JBKJS:' . $customer->jbkjs)
            );
            $custParty->appendChild($cPartyIdent);
        }
        // PartyName (naziv)
        $custName = $doc->createElement('cac:PartyName');
        $custName->appendChild($doc->createElement('cbc:Name', $customer->customer));
        $custParty->appendChild($custName);

        // Adresa Grad Postanski broj
        $cAddr = $doc->createElement('cac:PostalAddress');
        $cAddr->appendChild($doc->createElement('cbc:StreetName', $customer->address));
        $cAddr->appendChild($doc->createElement('cbc:CityName', $customer->city));
        $cAddr->appendChild($doc->createElement('cbc:PostalZone', $customer->postal_code));

        $cCountry = $doc->createElement('cac:Country');
        $cCountry->appendChild($doc->createElement('cbc:IdentificationCode', 'RS'));
        $cAddr->appendChild($cCountry);
        $custParty->appendChild($cAddr);
        $cTax = $doc->createElement('cac:PartyTaxScheme');
        $cTax->appendChild($doc->createElement('cbc:CompanyID', 'RS' . $customer->pib));
        $custParty->appendChild($cTax);
        $cLegal = $doc->createElement('cac:PartyLegalEntity');

        // Maticni broj
        $cLegal->appendChild($doc->createElement('cbc:CompanyID', $customer->mb));
        $custParty->appendChild($cLegal);
        $invoiceNode->appendChild($customerNode);

        // === Stavke ===
        $total = 0;
        foreach ($items as $i => $item) {
            $line = $doc->createElement('cac:InvoiceLine');
            $line->appendChild($doc->createElement('cbc:ID', $i + 1));
            $qty = $doc->createElement('cbc:InvoicedQuantity', number_format($item->pcs, 2, '.', ''));
            $qty->setAttribute('unitCode', 'C62');
            $line->appendChild($qty);
            $amount = $doc->createElement('cbc:LineExtensionAmount', number_format($item->pcs * $item->price, 2, '.', ''));
            $amount->setAttribute('currencyID', 'RSD');
            $line->appendChild($amount);

            $itemNode = $doc->createElement('cac:Item');
            $itemNode->appendChild($doc->createElement('cbc:Name', $item->article));
            $itemNode->appendChild($doc->createElement('cac:SellersItemIdentification'))->appendChild($doc->createElement('cbc:ID', $item->code));
            $taxCategory = $doc->createElement('cac:ClassifiedTaxCategory');
            $taxCategory->appendChild($doc->createElement('cbc:ID', 'E'));
            $taxCategory->appendChild($doc->createElement('cbc:Percent', '0'));
            $taxCategory->appendChild($doc->createElement('cbc:TaxExemptionReasonCode', 'PDV-RS-25-2-10'));
            $taxCategory->appendChild($doc->createElement('cbc:TaxExemptionReason', 'firma nije u sistemu PDV-a'));
            $taxCategory->appendChild($doc->createElement('cac:TaxScheme'))->appendChild($doc->createElement('cbc:ID', 'VAT'));
            $itemNode->appendChild($taxCategory);
            $line->appendChild($itemNode);

            $price = $doc->createElement('cac:Price');
            $priceAmount = $doc->createElement('cbc:PriceAmount', number_format($item->price, 2, '.', ''));
            $priceAmount->setAttribute('currencyID', 'RSD');
            $price->appendChild($priceAmount);
            $line->appendChild($price);

            $invoiceNode->appendChild($line);
            $total += $item->pcs * $item->price;
        }

        // === Total ===
        $taxTotal = $doc->createElement('cac:TaxTotal');
        $taxAmount = $doc->createElement('cbc:TaxAmount', '0.00');
        $taxAmount->setAttribute('currencyID', 'RSD');
        $taxTotal->appendChild($taxAmount);
        $invoiceNode->appendChild($taxTotal);

        $legalMonetaryTotal = $doc->createElement('cac:LegalMonetaryTotal');
        foreach (['LineExtensionAmount', 'TaxExclusiveAmount', 'TaxInclusiveAmount', 'PayableAmount'] as $key) {
            $node = $doc->createElement("cbc:{$key}", number_format($total, 2, '.', ''));
            $node->setAttribute('currencyID', 'RSD');
            $legalMonetaryTotal->appendChild($node);
        }
        $invoiceNode->appendChild($legalMonetaryTotal);

        return [
            'xml' => $doc->saveXML(),
            'document_id' => $documentId,
        ];
    }


}

