<?php


namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SEFXmlGenerator
{
    // KREIRANJE FAKTURE
    public function generate(CustomerInvoice $invoice): array //  vraća i XML i DocumentId zajedno — npr. kao asocijativni niz (array), umesto samo stringa
    {
        $company = config('sef');
        $customer = $invoice->customer;
        $items = $invoice->outputs;

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

//        // === Envelope ===
//        $envelope = $doc->createElementNS('urn:eFaktura:MinFinrs:envelop:schema', 'env:DocumentEnvelope');
//        $doc->appendChild($envelope);
//
//        // === Header ===
//        $header = $doc->createElement('env:DocumentHeader');
//        $envelope->appendChild($header);
//
//        // SalesInvoiceId i PurchaseInvoiceId SE NE DODAJU jer ih dodeljuje SEF
//        $header->appendChild($doc->createElement('env:DocumentId', $documentId));
//        $header->appendChild($doc->createElement('env:CreationDate', now()->format('Y-m-d')));
//        $header->appendChild($doc->createElement('env:SendingDate', now()->format('Y-m-d')));
//
//
//        // === DocumentBody ===
//        $body = $doc->createElement('env:DocumentBody');
//        $envelope->appendChild($body);

        // === Invoice sa namespace-ovima ===
        $invoiceNode = $doc->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cec', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sbt', 'http://mfin.gov.rs/srbdt/srbdtext');
        //$body->appendChild($invoiceNode);
        $doc->appendChild($invoiceNode);

        // === Obavezna polja ===
        $invoiceNode->appendChild($doc->createElement('cbc:CustomizationID', 'urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.rs:srbdt:2022'));
        $invoiceNode->appendChild($doc->createElement('cbc:ProfileID', 'urn:mfin.gov.rs:srbdt:01:2022'));
        $invoiceNode->appendChild($doc->createElement('cbc:ID', $invoice->invoice_number));
        $invoiceNode->appendChild($doc->createElement('cbc:IssueDate', $invoice->invoicing_date));
        $invoiceNode->appendChild($doc->createElement('cbc:DueDate', $invoice->invoicing_date));
        $invoiceNode->appendChild($doc->createElement('cbc:InvoiceTypeCode', '380'));
        $invoiceNode->appendChild($doc->createElement('cbc:DocumentCurrencyCode', 'RSD'));

        // === Supplier
        $supplier = $doc->createElement('cac:AccountingSupplierParty');
        $party = $doc->createElement('cac:Party');

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
        $taxScheme->appendChild($doc->createElement('cbc:TaxLevelCode', '0'));
        $taxScheme->appendChild($doc->createElement('cbc:ExemptionReasonCode', 'PDV-RS-25-2-10'));
        $taxScheme->appendChild($doc->createElement('cbc:ExemptionReason', 'firma nije u sistemu PDV-a'));
        $scheme = $doc->createElement('cac:TaxScheme');
        $scheme->appendChild($doc->createElement('cbc:ID', 'VAT'));
        $scheme->appendChild($doc->createElement('cbc:Name', 'PDV'));
        $scheme->appendChild($doc->createElement('cbc:TaxTypeCode', 'VAT'));
        $taxScheme->appendChild($scheme);
        $party->appendChild($taxScheme);

        $legalEntity = $doc->createElement('cac:PartyLegalEntity');
        $legalEntity->appendChild($doc->createElement('cbc:CompanyID', $company['mb']));
        $party->appendChild($legalEntity);

        $supplier->appendChild($party);
        $invoiceNode->appendChild($supplier);


        // === Customer
        $customerNode = $doc->createElement('cac:AccountingCustomerParty');
        $custParty = $doc->createElement('cac:Party');

        $cEnd = $doc->createElement('cbc:EndpointID', $customer->pib);
        $cEnd->setAttribute('schemeID', '9948');
        $custParty->appendChild($cEnd);

        if (!empty($customer->jbkjs)) {
            $cPartyIdent = $doc->createElement('cac:PartyIdentification');
            $cPartyIdent->appendChild($doc->createElement('cbc:ID', 'JBKJS:' . $customer->jbkjs));
            $custParty->appendChild($cPartyIdent);
        }

        $custName = $doc->createElement('cac:PartyName');
        $custName->appendChild($doc->createElement('cbc:Name', $customer->customer));
        $custParty->appendChild($custName);

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
        $cTax->appendChild($doc->createElement('cbc:TaxLevelCode', '0'));
        $cTax->appendChild($doc->createElement('cbc:ExemptionReasonCode', 'PDV-RS-25-2-10'));
        $cTax->appendChild($doc->createElement('cbc:ExemptionReason', 'firma nije u sistemu PDV-a'));
        $taxScheme = $doc->createElement('cac:TaxScheme');
        $taxScheme->appendChild($doc->createElement('cbc:ID', 'VAT'));
        $taxScheme->appendChild($doc->createElement('cbc:Name', 'PDV'));
        $taxScheme->appendChild($doc->createElement('cbc:TaxTypeCode', 'VAT'));
        $cTax->appendChild($taxScheme);
        $custParty->appendChild($cTax);

        $cLegal = $doc->createElement('cac:PartyLegalEntity');
        $cLegal->appendChild($doc->createElement('cbc:RegistrationName', $customer->customer));
        $cLegal->appendChild($doc->createElement('cbc:CompanyID', $customer->mb));
        $custParty->appendChild($cLegal);

        $customerNode->appendChild($custParty);
        $invoiceNode->appendChild($customerNode);

        // === BuyerCustomerParty ===
        $buyerCustomerParty = $doc->createElement('cac:BuyerCustomerParty');
        $buyerParty = $doc->createElement('cac:Party');

// EndpointID (PIB kupca)
        $endpoint = $doc->createElement('cbc:EndpointID', $customer->pib);
        $endpoint->setAttribute('schemeID', '9948');
        $buyerParty->appendChild($endpoint);

// JBKJS identifikacija
        if (!empty($customer->jbkjs)) {
            $buyerIdentification = $doc->createElement('cac:PartyIdentification');
            $buyerIdentification->appendChild(
                $doc->createElement('cbc:ID', 'JBKJS:' . $customer->jbkjs)
            );
            $buyerParty->appendChild($buyerIdentification);
        }

// Naziv kupca
        $buyerName = $doc->createElement('cac:PartyName');
        $buyerName->appendChild($doc->createElement('cbc:Name', $customer->customer));
        $buyerParty->appendChild($buyerName);

// Adresa kupca
        $buyerAddress = $doc->createElement('cac:PostalAddress');
        $buyerAddress->appendChild($doc->createElement('cbc:StreetName', $customer->address));
        $buyerAddress->appendChild($doc->createElement('cbc:CityName', $customer->city));
        $buyerAddress->appendChild($doc->createElement('cbc:PostalZone', $customer->postal_code));
        $buyerCountry = $doc->createElement('cac:Country');
        $buyerCountry->appendChild($doc->createElement('cbc:IdentificationCode', 'RS'));
        $buyerAddress->appendChild($buyerCountry);
        $buyerParty->appendChild($buyerAddress);

// Poreska šema
        $buyerTax = $doc->createElement('cac:PartyTaxScheme');
        $buyerTax->appendChild($doc->createElement('cbc:CompanyID', 'RS' . $customer->pib));
        $buyerTax->appendChild($doc->createElement('cbc:TaxLevelCode', '0'));
        $buyerTax->appendChild($doc->createElement('cbc:ExemptionReasonCode', 'PDV-RS-25-2-10'));
        $buyerTax->appendChild($doc->createElement('cbc:ExemptionReason', 'firma nije u sistemu PDV-a'));

        $buyerTaxScheme = $doc->createElement('cac:TaxScheme');
        $buyerTaxScheme->appendChild($doc->createElement('cbc:ID', 'VAT'));
        $buyerTaxScheme->appendChild($doc->createElement('cbc:Name', 'PDV'));
        $buyerTaxScheme->appendChild($doc->createElement('cbc:TaxTypeCode', 'VAT'));
        $buyerTax->appendChild($buyerTaxScheme);

        $buyerParty->appendChild($buyerTax);

// Pravno lice
        $buyerLegalEntity = $doc->createElement('cac:PartyLegalEntity');
        $buyerLegalEntity->appendChild($doc->createElement('cbc:RegistrationName', $customer->customer));
        $buyerLegalEntity->appendChild($doc->createElement('cbc:CompanyID', $customer->mb));
        $buyerParty->appendChild($buyerLegalEntity);

// Spoji sve
        $buyerCustomerParty->appendChild($buyerParty);
        $invoiceNode->appendChild($buyerCustomerParty);





        // 1. Izračunaj total
        $total = 0;
        foreach ($items as $item) {
            $total += $item->pcs * $item->price;
        }

        // === TaxTotal sa obaveznim TaxSubtotal ===
        $taxTotal = $doc->createElement('cac:TaxTotal');

        // Ukupan porez (kod firme van PDV sistema: 0.00)
        $taxAmount = $doc->createElement('cbc:TaxAmount', '0.00');
        $taxAmount->setAttribute('currencyID', 'RSD');
        $taxTotal->appendChild($taxAmount);

        // === TaxSubtotal (po specifikaciji SEF-a) ===
        $taxSubtotal = $doc->createElement('cac:TaxSubtotal');

        // Iznos oporezive osnovice
        $taxableAmount = $doc->createElement('cbc:TaxableAmount', number_format($total, 2, '.', ''));
        $taxableAmount->setAttribute('currencyID', 'RSD');
        $taxSubtotal->appendChild($taxableAmount);

        // Iznos poreza (0.00)
        $subTaxAmount = $doc->createElement('cbc:TaxAmount', '0.00');
        $subTaxAmount->setAttribute('currencyID', 'RSD');
        $taxSubtotal->appendChild($subTaxAmount);

        // Šifra kategorije PDV (npr. E = oslobođeno bez prava na odbitak)
        $taxCategory = $doc->createElement('cac:TaxCategory');
        $taxCategory->appendChild($doc->createElement('cbc:ID', 'E')); // BT-118
        $taxCategory->appendChild($doc->createElement('cbc:Percent', '0')); // BT-119

        $taxScheme = $doc->createElement('cac:TaxScheme');
        $taxScheme->appendChild($doc->createElement('cbc:ID', 'VAT'));
        $taxCategory->appendChild($taxScheme);

        $taxSubtotal->appendChild($taxCategory);
        $taxTotal->appendChild($taxSubtotal);

        // Dodaj u Invoice čvor
        $invoiceNode->appendChild($taxTotal);


        // === LegalMonetaryTotal
        $legalMonetaryTotal = $doc->createElement('cac:LegalMonetaryTotal');
        foreach (['LineExtensionAmount', 'TaxExclusiveAmount', 'TaxInclusiveAmount', 'PayableAmount'] as $key) {
            $node = $doc->createElement("cbc:{$key}", number_format($total, 2, '.', ''));
            $node->setAttribute('currencyID', 'RSD');
            $legalMonetaryTotal->appendChild($node);
        }
        $invoiceNode->appendChild($legalMonetaryTotal);

        // === InvoiceLine (POSLE totala, ispravno!)
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
            $itemNode->appendChild(
                $doc->createElement('cac:SellersItemIdentification')
            )->appendChild($doc->createElement('cbc:ID', $item->code));

            $taxCategory = $doc->createElement('cac:ClassifiedTaxCategory');
            $taxCategory->appendChild($doc->createElement('cbc:ID', 'E'));
            $taxCategory->appendChild($doc->createElement('cbc:Percent', '0'));
            $taxCategory->appendChild($doc->createElement('cbc:TaxExemptionReasonCode', 'PDV-RS-25-2-10'));
            $taxCategory->appendChild($doc->createElement('cbc:TaxExemptionReason', 'firma nije u sistemu PDV-a'));
            $taxScheme = $doc->createElement('cac:TaxScheme');
            $taxScheme->appendChild($doc->createElement('cbc:ID', 'VAT'));
            $taxCategory->appendChild($taxScheme);
            $itemNode->appendChild($taxCategory);
            $line->appendChild($itemNode);

            $price = $doc->createElement('cac:Price');
            $priceAmount = $doc->createElement('cbc:PriceAmount', number_format($item->price, 2, '.', ''));
            $priceAmount->setAttribute('currencyID', 'RSD');
            $price->appendChild($priceAmount);
            $line->appendChild($price);

            $invoiceNode->appendChild($line);
        }

        // === Kraj: vraćanje XML i UUID
        return [
            'xml' => $doc->saveXML(),
            'document_id' => (string) \Str::uuid(),
        ];
    }

    // STORNO FAKTURE
    public function generateCreditNote(CustomerInvoice $invoice): array
    {
        $company = config('sef');
        $customer = $invoice->customer;
        $items = $invoice->outputs;

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $invoiceNode = $doc->createElementNS(
            'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
            'Invoice'
        );

        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cec', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sbt', 'http://mfin.gov.rs/srbdt/srbdtext');
        $doc->appendChild($invoiceNode);

        $docId = (string) \Str::uuid();

        $invoiceNode->appendChild($doc->createElement('cbc:CustomizationID', 'urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.rs:srbdt:2022'));
        $invoiceNode->appendChild($doc->createElement('cbc:ProfileID', 'urn:mfin.gov.rs:srbdt:01:2022'));
        $invoiceNode->appendChild($doc->createElement('cbc:ID', 'STORNO-' . $invoice->invoice_number));
        $invoiceNode->appendChild($doc->createElement('cbc:IssueDate', now()->format('Y-m-d')));
        $invoiceNode->appendChild($doc->createElement('cbc:DueDate', now()->format('Y-m-d')));
        $invoiceNode->appendChild($doc->createElement('cbc:InvoiceTypeCode', '381')); // STORNO
        $invoiceNode->appendChild($doc->createElement('cbc:DocumentCurrencyCode', 'RSD'));

        // === Referenca na originalnu fakturu ===
        $billingReference = $doc->createElement('cac:BillingReference');
        $docRef = $doc->createElement('cac:InvoiceDocumentReference');
        $docRef->appendChild($doc->createElement('cbc:ID', $invoice->invoice_number));
        $docRef->appendChild($doc->createElement('cbc:IssueDate', $invoice->invoicing_date));
        $billingReference->appendChild($docRef);
        $invoiceNode->appendChild($billingReference);

        // === Dobavljač (kao u običnoj fakturi) ===
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

        // === Kupac ===
        $customerNode = $doc->createElement('cac:AccountingCustomerParty');
        $custParty = $doc->createElement('cac:Party');
        $customerNode->appendChild($custParty);
        $endpoint = $doc->createElement('cbc:EndpointID', $customer->pib);
        $endpoint->setAttribute('schemeID', '9948');
        $custParty->appendChild($endpoint);

        if (!empty($customer->jbkjs)) {
            $partyId = $doc->createElement('cac:PartyIdentification');
            $partyId->appendChild($doc->createElement('cbc:ID', 'JBKJS:' . $customer->jbkjs));
            $custParty->appendChild($partyId);
        }

        $custName = $doc->createElement('cac:PartyName');
        $custName->appendChild($doc->createElement('cbc:Name', $customer->customer));
        $custParty->appendChild($custName);
        $addr = $doc->createElement('cac:PostalAddress');
        $addr->appendChild($doc->createElement('cbc:StreetName', $customer->address));
        $addr->appendChild($doc->createElement('cbc:CityName', $customer->city));
        $addr->appendChild($doc->createElement('cbc:PostalZone', $customer->postal_code));
        $country = $doc->createElement('cac:Country');
        $country->appendChild($doc->createElement('cbc:IdentificationCode', 'RS'));
        $addr->appendChild($country);
        $custParty->appendChild($addr);
        $taxScheme = $doc->createElement('cac:PartyTaxScheme');
        $taxScheme->appendChild($doc->createElement('cbc:CompanyID', 'RS' . $customer->pib));
        $custParty->appendChild($taxScheme);
        $legalEntity = $doc->createElement('cac:PartyLegalEntity');
        $legalEntity->appendChild($doc->createElement('cbc:RegistrationName', $customer->customer));
        $legalEntity->appendChild($doc->createElement('cbc:CompanyID', $customer->mb));
        $custParty->appendChild($legalEntity);
        $invoiceNode->appendChild($customerNode);

        // === Stavke sa negativnim vrednostima ===
        $total = 0;
        foreach ($items as $i => $item) {
            $line = $doc->createElement('cac:InvoiceLine');
            $line->appendChild($doc->createElement('cbc:ID', $i + 1));
            $qty = $doc->createElement('cbc:InvoicedQuantity', number_format($item->pcs, 2, '.', ''));
            $qty->setAttribute('unitCode', 'C62');
            $line->appendChild($qty);

            $amount = $doc->createElement('cbc:LineExtensionAmount', number_format(-1 * $item->pcs * $item->price, 2, '.', ''));
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

        // === TaxTotal sa obaveznim TaxSubtotal ===
        $taxTotal = $doc->createElement('cac:TaxTotal');

        // Ukupan porez (kod firme van PDV sistema: 0.00)
        $taxAmount = $doc->createElement('cbc:TaxAmount', '0.00');
        $taxAmount->setAttribute('currencyID', 'RSD');
        $taxTotal->appendChild($taxAmount);

        // === TaxSubtotal (po specifikaciji SEF-a) ===
        $taxSubtotal = $doc->createElement('cac:TaxSubtotal');

        // Iznos oporezive osnovice
        $taxableAmount = $doc->createElement('cbc:TaxableAmount', number_format(-1 * $total, 2, '.', ''));

        $taxableAmount->setAttribute('currencyID', 'RSD');
        $taxSubtotal->appendChild($taxableAmount);

        // Iznos poreza (0.00)
        $subTaxAmount = $doc->createElement('cbc:TaxAmount', '0.00');
        $subTaxAmount->setAttribute('currencyID', 'RSD');
        $taxSubtotal->appendChild($subTaxAmount);

        // Šifra kategorije PDV (npr. E = oslobođeno bez prava na odbitak)
        $taxCategory = $doc->createElement('cac:TaxCategory');
        $taxCategory->appendChild($doc->createElement('cbc:ID', 'E')); // BT-118
        $taxCategory->appendChild($doc->createElement('cbc:Percent', '0')); // BT-119

        $taxScheme = $doc->createElement('cac:TaxScheme');
        $taxScheme->appendChild($doc->createElement('cbc:ID', 'VAT'));
        $taxCategory->appendChild($taxScheme);

        $taxSubtotal->appendChild($taxCategory);
        $taxTotal->appendChild($taxSubtotal);

        // Dodaj u Invoice čvor
        $invoiceNode->appendChild($taxTotal);


        // === LegalMonetaryTotal ===
        $legalMonetaryTotal = $doc->createElement('cac:LegalMonetaryTotal');
        foreach (['LineExtensionAmount', 'TaxExclusiveAmount', 'TaxInclusiveAmount', 'PayableAmount'] as $key) {
            $node = $doc->createElement("cbc:{$key}", number_format(-1 * $total, 2, '.', ''));
            $node->setAttribute('currencyID', 'RSD');
            $legalMonetaryTotal->appendChild($node);
        }
        $invoiceNode->appendChild($legalMonetaryTotal);

        return [
            'xml' => $doc->saveXML(),
            'document_id' => $docId,
        ];
    }


}

