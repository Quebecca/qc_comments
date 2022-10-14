if (document.getElementById('commentForm') !== null) {
    let siteKey = document.getElementById('sitekey').getAttribute('data-tr-label') !== null ? document.getElementById('sitekey').getAttribute('data-tr-label') : '';
    window.addEventListener("DOMContentLoaded", function () {
        $('#commentForm').submit(function (e) {
            grecaptcha.ready(function (token) {
                grecaptcha.execute(siteKey, {action: 'submit'}).then(function (token) {
                    return true;
                });
            });
        })
    });

    function onCompleted() {
        $('form .powermail_submit').prop("disabled", true);
        setTimeout(function enable() {
            submitAmount = 0;
            $('form .powermail_submit').prop("disabled", false);
        }, 2000);
        $('#commentForm').trigger('submit', [true]);
    }

    let maxCharacters = document.getElementById('maxCharacters').getAttribute('data-tr-label') != null
        ? document.getElementById('maxCharacters').getAttribute('data-tr-label') : '';
    let limitLabel = document.getElementById('limitLabel');
    textareaElement = document.getElementById('comment-textarea');
    let initialLimit = Number(maxCharacters);

    maxLabel = document.getElementById('maxLabel').getAttribute('data-tr-label')
    charLabel = document.getElementById('charLabel').getAttribute('data-tr-label')


    textareaElement.setAttribute("maxlength", maxCharacters);
    textareaElement.addEventListener("keydown", (event) => {
        var length = textareaElement.value.length
        var x = initialLimit === length ? 0
            : (length === 0 ? initialLimit
                : (initialLimit - length));
        if (event.code !== 'Backspace' && x >= 1) {
            limitLabel.innerText = maxLabel + (x-1) + charLabel;
        }
        else{
            if(x !== initialLimit && event.code === 'Backspace'){
                limitLabel.innerText = maxLabel + (x+1) + charLabel;
            }
            else if(initialLimit === length){
                limitLabel.innerText = maxLabel + "0"+ charLabel;
            }

        }
    });

    [
        document.getElementById('usefulN'),
        document.getElementById('usefulY')
    ].map(function (element) {
        element.addEventListener("change", function () {
            document.getElementById('comment-section').className = 'form-group d-block';
            document.getElementById('questionsLink-section').className = 'col-12 col-lg-4 flex-wrap flex-lg-wrap-reverse sansBorder d-block';
        });
    });

}
