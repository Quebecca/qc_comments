var submitAmount = 1;

$( document ).ready(function() {
    let isFormSubmittedElement = document.getElementById('isFormSubmitted');
    if(isFormSubmittedElement !== null){
        let submitted = isFormSubmittedElement.getAttribute('data-tr-label')
        if(['true', 'false'].includes(submitted)){
            let anchor = document.getElementById('comments-form-section');
            anchor.scrollIntoView()
        }
    }
    if (document.getElementById('commentForm') !== null) {

        let maxCharacters = document.getElementById('maxCharacters').getAttribute('data-tr-label') ?? '';
        let minCharacters = document.getElementById('minCharacters').getAttribute('data-tr-label') ?? '';
        let skipRecaptcha = document.getElementById('skipRecaptcha').getAttribute('data-tr-label') ?? '';

        [
            document.getElementById('usefulN'),
            document.getElementById('usefulY')
        ].map(function (element) {
            element.addEventListener("change", function () {
                document.getElementById('comment-section').className = 'form-group d-block';
                document.getElementById('questionsLink-section').className = 'col-12 col-lg-4 flex-wrap flex-lg-wrap-reverse d-block';
            });
        });


        $('#comment-textarea').on('keyup', function () {
            checkCommentLength();
        });

        $('#submitButton').click(function (event) {
            if (!skipRecaptcha && commentValidation()) {
                $('#submitButton').prop("disabled", true);
                $('#commentForm').trigger('submit', [true]);

                return true;
            }
            if(commentValidation() === false){
                event.preventDefault()
            }

            $('#comment-textarea').on('keyup', function () {
                commentValidation();
            });

            if(commentValidation() === true){
                $('#commentForm').trigger('submit', [true]);
            }
        });

        $('#commentForm').submit(function (event) {
            let form = $(this)
            let widgetId = form.find('.g-recaptcha').data('widget-id');
            if (typeof widgetId === 'undefined') {
                return;
            }
            if (typeof grecaptcha == 'object' && commentValidation() == true) {
                if (!grecaptcha.getResponse(widgetId)) {
                    event.preventDefault();
                    grecaptcha.execute(widgetId);
                }
            } else {
                event.preventDefault();
            }

        })

        function commentValidation() {
            let textareaElement = $('#comment-textarea');
            let commentLength = textareaElement.val().length;
            let validComment = ( minCharacters <= commentLength && commentLength <= maxCharacters ) || commentLength === 0;
            $('#submitButton').attr('disabled', !validComment)
            $('#error-message-too-short').toggleClass('d-none', validComment)
            $(textareaElement).toggleClass('error-textarea',!validComment)
            if(validComment == false){
                submitAmount = 0;
            }
            return validComment;
        }

        function checkCommentLength(){
            let maxLabel = document.getElementById('maxLabel').getAttribute('data-tr-label')
            let charLabel = document.getElementById('charLabel').getAttribute('data-tr-label')
            let currentLength = maxCharacters -  $('#comment-textarea').val().length
            if(currentLength >= 0){
                document.getElementById('limitLabel').innerHTML = maxLabel + currentLength + charLabel
            }
        }
    }

});
// Render reCAPTCHA
function onloadCallback () {
    $(".g-recaptcha").each(function (index) {
        $(this).data('widget-id', index);
        let key = $(this).attr('data-sitekey');
        grecaptcha.render($(this).attr('id'), {
            'sitekey': key,
            'callback': onCompleted
        });
    });
};
function onCompleted() {
    $('#submitButton').prop("disabled", true);
    if (submitAmount <= 1) {
        $('#commentForm').trigger('submit', [true]);
    }
    submitAmount++;
    grecaptcha.reset();

}