
if (document.getElementById('commentForm') !== null) {

    let siteKey = document.getElementById('sitekey').getAttribute('data-tr-label') ?? '';
    let maxCharacters = document.getElementById('maxCharacters').getAttribute('data-tr-label') ?? '';
    let minCharacters = document.getElementById('minCharacters').getAttribute('data-tr-label') ?? '';
    let skipRecaptcha = document.getElementById('skipRecaptcha').getAttribute('data-tr-label') ?? '';
    // Afficher le text-area quand on sélectionne "Oui" ou "Non"
    [
        document.getElementById('usefulN'),
        document.getElementById('usefulY')
    ].map(function (element) {
        element.addEventListener("change", function () {
            document.getElementById('comment-section').className = 'form-group d-block';
            document.getElementById('questionsLink-section').className = 'col-12 col-lg-4 flex-wrap flex-lg-wrap-reverse sansBorder d-block';
        });
    });

    window.addEventListener("DOMContentLoaded", function () {
        var submitAmount = 0;
        $('#comment-textarea').on('keyup', function () {
            checkCommentLength();
        });
        $('#commentForm').submit(function (event) {
                if (skipRecaptcha === '0' && commentValidation()) {
                    return true;
                }
                if(!commentValidation()){
                    event.preventDefault()
                }
                $('#comment-textarea').on('keyup', function () {
                    commentValidation();
                });

                if (typeof grecaptcha == 'object') {
                    if (!grecaptcha.getResponse()) {
                        event.preventDefault();
                        grecaptcha.execute();
                    }
                } else {
                    event.preventDefault();
                }
            });
           $('#commentForm #submitButton').mousedown(function (event) {
                var field = this;
                if (submitAmount === 0) {
                    submitAmount++;
                    field.disabled = true;
                    setTimeout(function enable() {
                        submitAmount = 0;
                        field.disabled = false;
                    }, 6000);
                    $('#commentForm').trigger('submit');
                }
            });

    });

    function onCompleted() {
        $('#submitButton').prop("disabled", true);
       // document.getElementById('comment-section').className = 'form-group d-block';
       // document.getElementById('questionsLink-section').className = 'col-12 col-lg-4 flex-wrap flex-lg-wrap-reverse sansBorder d-block';
       setTimeout(function enable() {
            submitAmount = 0;
            $('#submitButton').prop("disabled", false);
        }, 2000);
        $('#commentForm').trigger('submit', [true]);
    }

    function commentValidation() {
        let textareaElement = $('#comment-textarea');
        var commentLength = textareaElement.val().length;
        let validComment = ( minCharacters <= commentLength && commentLength <= maxCharacters ) || commentLength === 0;
        $('#submitButton').attr('disabled', !validComment)
        $('#error-message-too-short').toggleClass('d-none', validComment)
        $(textareaElement).toggleClass('error-textarea',!validComment)
        return validComment;
    }

    function checkCommentLength(){
        maxLabel = document.getElementById('maxLabel').getAttribute('data-tr-label')
        charLabel = document.getElementById('charLabel').getAttribute('data-tr-label')
        var currentLength = maxCharacters -  $('#comment-textarea').val().length
        if(currentLength >= 0){
            document.getElementById('limitLabel').innerHTML = maxLabel + currentLength + charLabel
        }
        /*if(currentLength <= 0){
            $('#comment-textarea').attr("maxlength",0);
        }*/
    }
}