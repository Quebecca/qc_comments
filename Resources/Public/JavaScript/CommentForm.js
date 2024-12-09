

$(document).ready(function(){
    var selectedForm;
    var submitAmount = 0;
    var parsleyError = "parsley-error";
    var formError = false;
// Render reCAPTCHA
    var onloadCallback = function () {
        $(".g-recaptcha").each(function (index) {
            // widget-id : used for grecaptcha method, to know which form is being submitted
            $(this).data('widget-id', index);
            let key = $(this).attr('data-sitekey');
            grecaptcha.render($(this).attr('id'), {
                'sitekey': key,
                'callback': onCompleted
            });
        });

    };
    let textareaElement = $('#comment-textarea');
    let isFormSubmittedElement = document.getElementById('isFormSubmitted');
    let isPositifCommentSubmitted = false;
    let positifFormUpdate = false;
    if (isFormSubmittedElement !== null) {
        let submitted = isFormSubmittedElement.getAttribute('data-ts')
        if (['true', 'false'].includes(submitted)) {
            let anchor = document.getElementById('comments-form-section');
            anchor.scrollIntoView()
        }
    }

    if ($('#QcCommentForm').length > 0) {
        let maxCharacters = document.getElementById('maxCharacters').getAttribute('data-ts') ?? '';
        let minCharacters = document.getElementById('minCharacters').getAttribute('data-ts') ?? '';
        let enableRecaptcha = document.getElementById('enableRecaptcha').getAttribute('data-ts') ?? '';

        let clickedButtonId = '1';
        $('#submitButtonYes').on('click', function () {
            clickedButtonId = $(this).attr('id')
        })
        let usefulBlock = $('.useful-block')
        // Show success message
        let isUsefulFormSubmitted = document.getElementById('isUsfulFormSubmitted').getAttribute('data-useful-form-submitted') ?? '';
        if (isUsefulFormSubmitted === '1') {
            usefulBlock.attr('class',usefulBlock.attr('class') + 'd-none');
            $('.submit-section').attr('class', 'submit-section d-none');
            $('.text-area-comments ').hide()
            $('.form-submission-success').attr('class', 'form-submission-success')
        }

        $('#negative-button, #report-problem').on('click', function (event) {
            event.preventDefault();
            $('.submit-section').attr('class', 'submit-section');
            $('.text-area-comments').attr('class', 'text-area-comments');
            let commentType = $('#comment-type')
            let usefulBlockClass = usefulBlock.attr('class')
            usefulBlock.attr('class',usefulBlockClass + 'd-none');

            $('.form-section').show()
            $('.comment-textarea').hide()
            $('.positif-comment-section').hide()
            isPositifCommentSubmitted = false;
            positifFormUpdate = false;
            if($(this).attr('id') === 'report-problem'){
                let reportProblemSection = $('.report-problem-section')
                reportProblemSection.attr('class', 'report-problem-section');
                commentType.attr('value', 'NA');
                reportProblemSection.show()
                $('.negative-report-options').hide();
                $('.report-problem-options').show();
            }
            else {
                let negatifReportSection = $('.negative-report-section')
                negatifReportSection.attr('class', 'negative-report-section');
                commentType.attr('value', '0');
                negatifReportSection.show()
                $('.report-problem-options').hide();
                $('.negative-report-options').show();
            }
        })


        let submitAmount = 0;
        $(textareaElement).on('keyup', function () {
            let commentLength = textareaElement.val().length;
            checkMaxCommentLength(commentLength)
        });

        // Check if an option is selected to show/hide selecting option message error
        let optionsSelected = false;
        $('.report-problem-options, .negative-report-options').find('input[type=radio]').on('click', function () {
            $('.options-error-message').hide();
            $('#submitButton').attr('disabled', false);

            optionsSelected = !optionsSelected;
        })

        // Check if the submit button is clicked to trigger the comment validation on keyup
        let submitButtonClicked = false;
        $('#QcCommentForm #submitButton').on('click', function (event) {
            submitButtonClicked = true;
        });

        // Trigger the validation error css on keyup
        $(textareaElement).on('keyup', function () {
            let submitButton = $('#submitButton')
            submitButton.attr('disabled', !checkMaxCommentLength());

            if (clickedButtonId !== 'submitButtonYes' && isPositifCommentSubmitted === false) {
                submitButton.attr('disabled', !checkIfCommentEmpty());
            }

        });

        function commentValidation() {
            let errorOptionsMessage = $('.options-error-message');
            if($('input[type=radio]:visible:checked').length > 0){
                $('.options-error-message').attr('class', 'options-error-message d-none');
                optionsSelected = true;
            }
            else {
                errorOptionsMessage.show()
                errorOptionsMessage.attr('class', 'options-error-message');
                optionsSelected = false;
            }

            let valid = true;
            if (positifFormUpdate === false) {
                valid = checkIfCommentEmpty()
                    && checkMaxCommentLength()
                    && checkMinCommentLength() && optionsSelected;
            }
            else {
                valid = checkMaxCommentLength()
                    && checkMinCommentLength();
            }
            $('#submitButton').attr('disabled', !valid);

            let messageError = $('.maxChars:visible, .parsley-custom-error-message:visible').first()

            if (valid === false && messageError.length > 0) {
                $('html, body').animate({
                    scrollTop: $(messageError)
                        .parent().parent().parent().parent().offset()?.top
                });
            }

            formError = !valid;
            if (formError === false)
                submitAmount = 0;
            return valid;

        }
        /**
         * This function is used to check if the inserted comment length is higher than the allow minimum number of characters
         * @returns {boolean}
         */
        function checkMinCommentLength(){
            let commentLength = textareaElement.val().length;
            if(commentLength < minCharacters){
                let atLeastXChars = document.getElementById('atLeastXChars').getAttribute('data-ts')
                if(commentLength > 0 && commentLength < minCharacters){
                    document.getElementById('limitLabel').innerHTML =`<span id="maxExceeded">${atLeastXChars}</span>`;
                }
            }
            $(textareaElement).toggleClass('error-textarea', (commentLength < minCharacters && commentLength !== 0));

            return commentLength >= minCharacters || commentLength === 0;
        }

        /**
         * This function is used to check if the comment characters length exceeds the maximum allowed
         * @returns {boolean}
         */
        function checkMaxCommentLength(){
            let commentLength = textareaElement.val().length;
            let charLabel = document.getElementById('charLabel').getAttribute('data-ts')
            let tooManyChars = document.getElementById('tooManyChars').getAttribute('data-ts')
            let currentLength = maxCharacters - commentLength
            // Show error message "max chars exceed"
            if (currentLength >= 0){
                document.getElementById('limitLabel').innerHTML = currentLength + charLabel
            }
            if(currentLength < 0){
                document.getElementById('limitLabel').innerHTML =`<span id="maxExceeded">${Math.abs(currentLength)} ${tooManyChars}</span>`;
            }
            (textareaElement).toggleClass('error-textarea', commentLength > maxCharacters);
            return commentLength <= maxCharacters;

        }

        /**
         * This function is used to check if the comment area is empty or not
         * @returns {boolean}
         */
        function checkIfCommentEmpty(){
            let commentLength = textareaElement.val().length;
            let errorMessage =  $('#error-message-empty-comment')
            errorMessage.show()
            errorMessage.toggleClass('d-none', commentLength !== 0);
            (textareaElement).toggleClass('error-textarea',  commentLength === 0);
            return commentLength !== 0;
        }

    }


    $('#submitButton').on('click', function(event){
        // Check if reCAPTCHA is enabled
        let isRecaptchaEnabled = document.getElementById('enableRecaptcha')?.getAttribute('data-ts') ?? '';
        if (isRecaptchaEnabled === '1') {
            // Get the reCAPTCHA widget ID
            let widgetId = $("#QcCommentForm").find('.g-recaptcha').data('widget-id');

            if (typeof widgetId === 'undefined') {
                console.warn("reCAPTCHA widget ID is undefined.");
                return; // Stop further execution if widget ID is not found
            }

            // Check if the reCAPTCHA object exists
            if (typeof grecaptcha === 'object') {
                // If the reCAPTCHA response is empty, execute the reCAPTCHA challenge
                if (!grecaptcha.getResponse(widgetId) || grecaptcha.getResponse(widgetId) !== '') {
                    event.preventDefault(); // Prevent the default action
                    grecaptcha.execute(widgetId); // Trigger the reCAPTCHA
                    isPositifCommentSubmitted = false;
                    onCompleted()
                }
            } else {
                console.error("reCAPTCHA object is not available.");
                event.preventDefault(); // Prevent the default action since reCAPTCHA is required
                return;
            }
        } else {
            return true;
        }
    })

    $('#QcCommentForm').submit(function (event) {
        // After submission, we will point on the submission message
        if(enableRecaptcha !== 1){
            if(commentValidation()){
                $('#submitButton').prop("disabled", true);
                return true;
            }
            else{
                event.preventDefault();
                submitAmount = 0;
            }
        }
    });
    function onCompleted () {
        // Trigger the validation for Qc Comments form
        if(isPositifCommentSubmitted === false){
            commentValidation();
        }
        // Prevent multiple submissions
        if (submitAmount === 0 && formError === false) {
            if(isPositifCommentSubmitted === false){
                $('#QcCommentForm').trigger('submit', [true]);
                submitAmount++;
            }
            else{
                positifFormUpdate = true;
            }
        }
        grecaptcha.reset();
    }

    $('.cancel-button').on('click', function(event){
        event.preventDefault()
        let useFulBlock = $('.useful-block');
        let commentField = $("#comment-textarea")
        let usefulBlockClass =  useFulBlock.attr('class').replace('d-none','')
        useFulBlock.attr('class', usefulBlockClass);

        $('.negative-report-section').hide()
        $('.report-problem-section').hide()
        $('.submitted-form-message-section').hide()
        $('.form-section').hide();
        $('#error-message-empty-comment').hide()
        $('.options-error-message').hide()
        commentField.attr('class', 'form-control');
        commentField.val('');
        $('.report-problem-options input[type=radio]:visible, .negative-report-options input[type=radio]:visible').each(function () {
            if ($(this).prop('checked')) {
                $(this).prop('checked', false);
            }
        });

        $('#submitButton').attr('disabled', false);

    })
    let  addInputType = function () {
        $('#comment-type').attr('value', '1')
    }


    $('#positif-button').on('click', function(event){
        event.preventDefault();
        let positifCommentSection = $('.positif-comment-section');
        let useFulBlock = $('.useful-block');
        positifCommentSection.attr('class', 'positif-comment-section')
        positifCommentSection.show()
        $('.submit-section').attr('class', 'submit-section');
        $('.text-area-comments').attr('class', 'text-area-comments');
        let usefulBlocClass = useFulBlock.attr('class')
        useFulBlock.attr('class', usefulBlocClass+ ' d-none');
        $('.form-instruction').hide()
        $('.option').hide();
        $('.form-section').show()
        $('.submitted-form-message-section').show()
        $('.comment-textarea').hide()
        isPositifCommentSubmitted = true;
        positifFormUpdate = true;

    })
})

let onCompleted;
var onloadCallback = function () {
    $(".g-recaptcha").each(function (index) {
        // widget-id : used for grecaptcha method, to know which form is being submitted
        $(this).data('widget-id', index);
        let key = $(this).attr('data-sitekey');
        grecaptcha.render($(this).attr('id'), {
            'sitekey': key,
            'callback': onCompleted
        });
    });

};
// Set the selected form
function setForm(form) {
    selectedForm = form;
}
$(window).on("pageshow", function() {
    if($('#QcCommentForm').length > 0){
        $('#QcCommentForm')[0].reset();
    }
})


