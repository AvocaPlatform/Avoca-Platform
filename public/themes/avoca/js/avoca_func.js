function avocaAlert(title, content) {
    $.alert({
        title: title,
        content: content
    });
}

function sendMail() {
    $.get(manage_url + '/emails/popup_mail');
}