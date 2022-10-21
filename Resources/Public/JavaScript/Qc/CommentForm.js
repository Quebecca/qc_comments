$(document).ready(function(){

    if (document.getElementById('commentForm') !== null && document.getElementById('sitekey') !== null) {
        let siteKey = document.getElementById('sitekey').getAttribute('data-tr-label') ?? '';
        window.addEventListener("DOMContentLoaded", function () {
            $('#commentForm').submit(function (e) {
                grecaptcha.ready(function (token) {
                    grecaptcha.execute(siteKey, {action: 'submit'}).then(function (token) {
                        return true;
                    });
                });
            })
        });

        let maxCharacters = document.getElementById('maxCharacters').getAttribute('data-tr-label') ?? '';

        let limitLabel = document.getElementById('limitLabel');
        textareaElement = document.getElementById('comment-textarea');
        let initialLimit = Number(maxCharacters);

        maxLabel = document.getElementById('maxLabel').getAttribute('data-tr-label')
        charLabel = document.getElementById('charLabel').getAttribute('data-tr-label')


        textareaElement.setAttribute("maxlength", maxCharacters);
        textareaElement.addEventListener("keydown", (event) => {
            var length = textareaElement.value.length
            var limit = initialLimit === length ? 0
                : (length === 0 ? initialLimit
                    : (initialLimit - length));
            if (event.code !== 'Backspace' && limit >= 1) {
                limitLabel.innerText = maxLabel + (limit-1) + charLabel;
            }
            else{
                if(limit !== initialLimit && event.code === 'Backspace'){
                    limitLabel.innerText = maxLabel + (limit+1) + charLabel;
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
            });
        });

    }
});

function onCompleted() {
    $('form .powermail_submit').prop("disabled", true);
    $( "#usefulN" ).prop( "checked", false);
    $( "#usefulY" ).prop( "checked", false);
    $("#comment-textarea").val("");
    setTimeout(function enable() {
        submitAmount = 0;
        $('form .powermail_submit').prop("disabled", false);
    }, 2000);
    $('#commentForm').trigger('submit', [true]);
}



