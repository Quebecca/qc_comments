// Form validation
// comment length > 2 and < 500
// comment not empty
// if true disable submit button
// Il faut vérifier que usefull or non is checked


if (document.getElementById('commentForm') !== null) {
    let siteKeyElement = document.getElementById('sitekey');
    let siteKey = siteKeyElement.getAttribute('data-tr-label') ?? '';
    let maxCharacters = document.getElementById('maxCharacters').getAttribute('data-tr-label') ?? '';
    let minCharacters = document.getElementById('minCharacters').getAttribute('data-tr-label') ?? '';

    function commentValidation() {
        let textareaElement = $('#comment-textarea');
        var commentLength = textareaElement.val().length;
        let validComment = ( minCharacters <= commentLength && commentLength <= maxCharacters ) || commentLength === 0;
        $('#submitButton').attr('disabled', !validComment)
        $('#error-message-to-short').toggleClass('d-none', validComment)
        $(textareaElement).toggleClass('error-textarea',!validComment)
        return validComment;
    }

    window.addEventListener("DOMContentLoaded", function () {

        $('#comment-textarea').on('keyup', function () {
            checkCommentLength();
        });

        $('#commentForm').submit(function (e) {
            if(!commentValidation())
                e.preventDefault()
            $('#comment-textarea').on('keyup', function () {
                commentValidation();
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

    function checkCommentLength(){
        maxLabel = document.getElementById('maxLabel').getAttribute('data-tr-label')
        charLabel = document.getElementById('charLabel').getAttribute('data-tr-label')
        document.getElementById('limitLabel').innerHTML = maxLabel + (maxCharacters -  $('#comment-textarea').val().length) + charLabel

    }

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
