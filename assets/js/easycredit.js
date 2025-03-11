function oscHandlePaymentCheckoutEvents()
{
    let result = document.querySelectorAll('INPUT[type=radio][name=paymentid]');
    if (result.length > 0) {
        for (var i = 0; i < result.length; i++) {
            result[i].addEventListener('change', oscHandlePaymentRadioChange);
        }
    }

    let elemPaymentRadio = document.getElementById('payment_easycreditinstallment');
    if (elemPaymentRadio && elemPaymentRadio.checked === true) {
        oscApexAddEvents();
    }
}

function oscHandlePaymentRadioChange(e)
{
    if (e.target.id === "payment_easycreditinstallment" && e.target.checked === true) {
        oscApexAddEvents();
    } else {
        oscApexRevertEvents();
    }
}

function oscApexAddEvents()
{
    let button = oscGetApexNextButton();
    if (button !== null) {
        button.onclick = null; // remove on click event from button and add an extended on click event which also submits the form
        button.removeAttribute("onclick");
        button.addEventListener('click', oscHandlePaymentForm);

        let paymentForm = document.getElementById('payment');
        paymentForm.addEventListener('submit', oscHandlePaymentForm);
    }
}

function oscApexRevertEvents()
{
    let button = oscGetApexNextButton();
    if (button !== null) {
        button.removeEventListener('click', oscHandlePaymentForm);
        button.setAttribute("onclick", "document.getElementById('payment').submit();");

        let paymentForm = document.getElementById('payment');
        paymentForm.removeEventListener('submit', oscHandlePaymentForm);
    }
}

function oscGetApexNextButton()
{
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

function oscHandlePaymentForm(e)
{
    var paymentForm = document.getElementById('payment');
    if (paymentForm.elements['payment_easycreditinstallment'].checked === true) {
        document.getElementById("easycredit_agreement_error").style.display = "none";
        if (document.getElementById("easycredit_agreement").checked === false) {
            e.preventDefault();
            document.getElementById("easycredit_agreement_error").style.display = "";
        } else {
            paymentForm.submit();
        }
        return true;
    } else {
        // default behaviour
        paymentForm.submit();
    }
}
