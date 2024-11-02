(function($) {
    $(window).on('load', function() {
        const debounce = (callback, wait) => {
            let timeoutId = null;
            return (...args) => {
                window.clearTimeout(timeoutId);
                timeoutId = window.setTimeout(() => {
                    callback(...args);
                }, wait);
            };
        }
        // Function to initialize focal point picker
        function initFocalPointPicker() {
            var focalPointImage = $('.media-modal:visible .focal-point-image', parent.document);
            var focalPointMarker = $('.media-modal:visible .focal-point-marker', parent.document);
            var focalPointInput = $('.media-modal:visible .compat-field-focal_point > td > input', parent.document);

            console.log("Focal point elements", focalPointImage, focalPointMarker, focalPointInput);
            if (focalPointImage.length > 0) {

                // Set initial marker position if values exist
                var initial = focalPointInput.val()
                var [initialX, initialY] = initial.split("x");
                if (initialX && initialY) {
                    focalPointMarker.css({
                        top: initialY + '%',
                        left: initialX + '%'
                    });
                } else {
                    focalPointMarker.css({
                        top: '50%',
                        left: '50%'
                    });
                }

                // Handle image click to set new focal point
                focalPointImage.off('click').on('click', function(event) {
                    var offset = $(this).offset();
                    var clickX = event.pageX - offset.left;
                    var clickY = event.pageY - offset.top;

                    var width = $(this).width();
                    var height = $(this).height();

                    var percentageX = (clickX / width) * 100;
                    var percentageY = (clickY / height) * 100;

                    // Move the marker to the clicked position
                    focalPointMarker.css({
                        top: percentageY + '%',
                        left: percentageX + '%'
                    });

                    // Save focal point values in hidden inputs
                    focalPointInput.val(percentageX.toFixed(2)+"x"+percentageY.toFixed(2)).trigger("change");
                });
            }
        }

        // Function to check if we are on the media details screen directly (after page load)
        function checkForMediaDetailsView() {
            console.log("checkForMediaDetailsView()");
            initFocalPointPicker();
        }

        // Listen for clicks in the media library
        $(parent.document).on('click', '.media-modal, .attachments-browser', debounce(function() {
            console.log('Media click event triggered', this);
            checkForMediaDetailsView();
        }, 500))

        // Run this on page load in case we are already on the attachment details view
        setTimeout(() => {
            checkForMediaDetailsView();
        }, 500);
    });
})(jQuery);
