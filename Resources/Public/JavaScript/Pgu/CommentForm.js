// Form validation
// comment length > 2 and < 500
// comment not empty
// if true disable submit button
// Il faut vérifier que usefull or non is checked

function commentValidation() {
    let maxCharacters = document.getElementById('maxCharacters').getAttribute('data-tr-label') ?? '';
    let minCharacters = document.getElementById('minCharacters').getAttribute('data-tr-label') ?? '';
    let textareaElement = $('#comment-textarea');
    var commentLength = textareaElement.val().length;

    return ( minCharacters < commentLength && commentLength < maxCharacters ) || commentLength === 0;

}
if (document.getElementById('commentForm') !== null) {
    let siteKeyElement = document.getElementById('sitekey');
    let siteKey = siteKeyElement.getAttribute('data-tr-label') ?? '';
    window.addEventListener("DOMContentLoaded", function () {

        $('#commentForm').submit(function (e) {
            let validComment = commentValidation();
            if(!validComment)
                e.preventDefault()
            $('#submitButton').attr('disabled', validComment)
            $('#error-message-to-short').toggleClass('d-none', validComment)

            $('#comment-textarea').on('keyup', function () {
                $(this).toggleClass('error-textarea',commentValidation() )
                $('#submitButton').attr('disabled', commentValidation())
                $('#error-message-to-short').toggleClass('d-none',commentValidation())
            })

            grecaptcha.ready(function (token) {
                grecaptcha.execute(siteKey, {action: 'submit'}).then(function (token) {
                    return true;
                });
            })
        })
    });


    // Recaptcha
    function onCompleted() {
        $('form .powermail_submit').prop("disabled", true);
        document.getElementById('comment-section').className = 'form-group d-block';
        document.getElementById('questionsLink-section').className = 'col-12 col-lg-4 flex-wrap flex-lg-wrap-reverse sansBorder d-block';
        setTimeout(function enable() {
            submitAmount = 0;
            $('form .powermail_submit').prop("disabled", false);
        }, 2000);
        $('#commentForm').trigger('submit', [true]);
    }



    // Comment content validation
    // Controller le commentaire au moment de saisi

    let maxCharacters = document.getElementById('maxCharacters').getAttribute('data-tr-label') ?? '';
    let minCharacters = document.getElementById('minCharacters').getAttribute('data-tr-label') ?? '';

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

    // Afficher le text-area quand on selectionne "Oui" ou "Non"
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
