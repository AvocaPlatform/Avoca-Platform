function avocaAlert(title, content) {
    $.alert({
        title: title,
        content: content
    });
}

function quickEdit(module, cb) {
    $.get(base_url + '/' + module + '/quick_edit', function (html_form) {
        // init modal
        $('#modal-application').html(html_form);
        $('#modal-application').modal({});
        // callback
        $('.QuickEditForm').submit(function (e) {
            e.preventDefault();
            if (cb) {
                cb();
            } else {
                var post_url = $(this).attr("action"); //get form action url
                var request_method = $(this).attr("method"); //get form GET/POST method
                var form_data = $(this).serialize(); //Encode form elements for submission
                $.ajax({
                    url : post_url,
                    type: request_method,
                    data : form_data
                }).done(function(res){ //
                    if (res.error) {
                        if (res.message) {
                            avocaAlert('Error', res.message);
                        } else {
                            avocaAlert('Error', 'Have some error. Please contact with administrator!');
                        }
                    } else {
                        if (res.message) {
                            avocaAlert('Success', res.message);
                        } else {
                            avocaAlert('Success', 'Success!');
                        }

                        $('#modal-application').modal('hide');
                    }
                });
            }
        });
    });
}

// send mail popup
function sendMail() {
    quickEdit('Emails');
}