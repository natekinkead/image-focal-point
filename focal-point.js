(function($) {
    $(window).on('load', function() {
        // Function to initialize focal point picker
        function initFocalPointPicker() {
            var focalPointImage = $('#focal-point-image');
            var focalPointMarker = $('#focal-point-marker');
            var focalPointInput = $('.compat-field-focal_point > td > input');

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
            if ($('.attachment-details').length > 0) {
                setTimeout(function() {
                    initFocalPointPicker();
                }, 200);
            }
        }

        // Ensure wp.media.frame is available before trying to attach events
        $(document).on('click', '.media-modal, .attachments-browser', function() {
            initFocalPointPicker();
        });

        // Run this on page load in case we are already on the attachment details view
        checkForMediaDetailsView();
    });
})(jQuery);
