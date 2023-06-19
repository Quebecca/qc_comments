$( document ).ready(function() {

    let isFormSubmittedElement = document.getElementById('isFormSubmitted');
    if(isFormSubmittedElement !== null){
        let submitted = isFormSubmitted.getAttribute('data-tr-label')
        if(['true', 'false'].includes(submitted)){
            let anchor = document.getElementById('comments-form-section');
            anchor.scrollIntoView()
        }
    }
    if (document.getElementById('commentForm') !== null) {


        let siteKey = document.getElementById('sitekey').getAttribute('data-tr-label') ?? '';
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

        var submitAmount = 0;
        var isRecaptchaLoaded = false;
        $('#commentForm :input').on('click',function (){
            if(!isRecaptchaLoaded && skipRecaptcha === true){
                var lang = $('#lang').attr('data-tr-label')
                var head = document.getElementsByTagName('head')[0];
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = 'https://www.google.com/recaptcha/api.js?render='+siteKey+'&hl='+lang;
                script.defer = true
                script.async = true
                head.appendChild(script);
                isRecaptchaLoaded = true
            }
        })

        $('#comment-textarea').on('keyup', function () {
            checkCommentLength();
        });
        $('#commentForm').submit(function (event) {

            if (!skipRecaptcha && commentValidation()) {
                $('#submitButton').prop("disabled", true);
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
            $('#submitButton').prop("disabled", true);

        });

        $('#commentForm #submitButton').mousedown(function (event) {
            var field = this;
            if (submitAmount === 0) {
                submitAmount++;
                field.disabled = true;
/*                setTimeout(function enable() {
                    submitAmount = 0;
                    field.disabled = false;
                }, 6000);*/
                $('#commentForm').trigger('submit');
            }
        });

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
});
function onCompleted() {
    $('#submitButton').prop("disabled", true);
    $('#commentForm').trigger('submit', [true]);
}