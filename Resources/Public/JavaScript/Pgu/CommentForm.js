// Form validation
// comment length > 2 and < 500
// comment not empty
// if true disable submit button
// Il faut vérifier que usefull or non is checked

if (document.getElementById('commentForm') !== null) {
    let siteKeyElement = document.getElementById('sitekey');
    let siteKey = siteKeyElement.getAttribute('data-tr-label') ?? '';
    window.addEventListener("DOMContentLoaded", function () {

        let maxCharacters = document.getElementById('maxCharacters').getAttribute('data-tr-label') ?? '';
        let minCharacters = document.getElementById('minCharacters').getAttribute('data-tr-label') ?? '';

        $('#commentForm').submit(function (e) {
            let textareaElement = $('#comment-textarea')
            var commentLenght = textareaElement.val().length;
            let condition = commentLenght < minCharacters || commentLenght > maxCharacters ;
            textareaElement.toggleClass('error-textarea',condition )
            $('#submitButton').attr('disabled', condition)
            $('#error-message-empty').toggleClass('d-none', (!condition && commentLenght !== 0))
            $('#error-message-to-short').toggleClass('d-none', (!condition && commentLenght === 0))

            if(condition)
                e.preventDefault()

            textareaElement.on('keyup', function () {
                commentLenght = $(this).val().length;
                let condition = commentLenght < minCharacters || commentLenght > maxCharacters;
                $(this).toggleClass('error-textarea',condition )
                $('#submitButton').attr('disabled', condition)
                $('#error-message-empty').toggleClass('d-none', (!condition && commentLenght !== 0))
                $('#error-message-to-short').toggleClass('d-none', (!condition && commentLenght === 0))

            })
            grecaptcha.ready(function (token) {
                grecaptcha.execute(siteKey, {action: 'submit'}).then(function (token) {
                    return true;
                });
            })
        })
    });

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
