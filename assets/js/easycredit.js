function oscHandlePaymentCheckoutEvents() {
    let result = document.querySelectorAll('INPUT[type=radio][name=paymentid]');
    if (result.length > 0) {
        for (var i = 0; i < result.length; i++) {
            result[i].addEventListener('change', oscHandlePaymentRadioChange);
        }
    }

    let elemInstallmentPaymentRadio = document.getElementById('payment_easycreditinstallment');
    let elemInvoicePaymentRadio = document.getElementById('payment_easycreditinvoice');
    let elemInstallmentContainer = document.getElementById('easycredit_installment_container');
    let elemInvoiceContainer = document.getElementById('easycredit_invoice_container');
    if (elemInstallmentContainer) {
        elemInstallmentContainer.style.display = 'none';
    }
    if (elemInvoiceContainer) {
        elemInvoiceContainer.style.display = 'none';
    }
    if ((elemInstallmentPaymentRadio && elemInstallmentPaymentRadio.checked === true) ||
        (elemInvoicePaymentRadio && elemInvoicePaymentRadio.checked === true)
    ) {
        oscApexAddEvents();
    }

    // payment is pre-selected
    if (elemInstallmentPaymentRadio && elemInstallmentPaymentRadio.checked === true){
        elemInstallmentContainer.style.display = 'block';
    }

    if (elemInvoicePaymentRadio && elemInvoicePaymentRadio.checked === true){
        elemInvoiceContainer.style.display = 'block';
    }
}

function oscHandlePaymentRadioChange(e) {
    let elemInstallmentContainer = document.getElementById('easycredit_installment_container');
    let elemInvoiceContainer = document.getElementById('easycredit_invoice_container');
    if (e.target.id === "payment_easycreditinstallment" && e.target.checked === true) {
        oscApexAddEvents();
        elemInstallmentContainer.style.display = 'block';
    } else {
        oscApexRevertEvents();
        elemInstallmentContainer.style.display = 'none';
    }

    if (e.target.id === "payment_easycreditinvoice" && e.target.checked === true) {
        if (elemInvoiceContainer) {
            oscApexAddEvents();
            elemInvoiceContainer.style.display = 'block';
        }
    } else {
        if (elemInvoiceContainer) {
            oscApexRevertEvents();
            elemInvoiceContainer.style.display = 'none';
        }
    }
}

function oscApexAddEvents() {
    let button = oscGetApexNextButton();
    if (button !== null) {
        button.onclick = null; // remove on click event from button and add an extended on click event which also submits the form
        button.removeAttribute("onclick");
        button.addEventListener('click', oscHandlePaymentForm);

        let paymentForm = document.getElementById('payment');
        paymentForm.addEventListener('submit', oscHandlePaymentForm);
    }
}

function oscApexRevertEvents() {
    let button = oscGetApexNextButton();
    if (button !== null) {
        button.removeEventListener('click', oscHandlePaymentForm);
        button.setAttribute("onclick", "document.getElementById('payment').submit();");

        let paymentForm = document.getElementById('payment');
        paymentForm.removeEventListener('submit', oscHandlePaymentForm);
    }
}

function oscGetApexNextButton() {
    let button = null;

    let result = document.querySelectorAll("BUTTON.btn.btn-highlight.btn-lg.w-100");
    if (result.length === 1) {
        button = result[0];
    } else {
        for (var i = 0; i < result.length; i++) {
            button = result[i];
            break;
        }
    }

    return button;
}

function oscHandlePaymentForm(e) {
    var paymentForm = document.getElementById('payment');

    if (paymentForm.elements['payment_easycreditinstallment'].checked === true) {
        if (document.getElementById("easycredit_installment_agreement_error")) {
            document.getElementById("easycredit_installment_agreement_error").style.display = "none";
            if (document.getElementById("easycredit_installment_agreement").checked === false) {
                e.preventDefault();
                document.getElementById("easycredit_installment_agreement_error").style.display = "block";
            } else {
                paymentForm.submit();
            }
        }
    }
    return true
}
