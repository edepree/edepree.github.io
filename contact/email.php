<?php

$contact_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$contact_email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$contact_subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
$contact_message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

if(!empty($contact_name) && !empty($contact_email) && !empty($contact_subject) && !empty($contact_message))
{
    $contactInformation = "Email From: $contact_name <$contact_email>\r\n\r\n";
    $contact_message = $contactInformation . $contact_message;

    $headers = "From: EDEPREE.COM <contact@edepree.com>\r\n";
    $acceptedForDelivery = mail('eric.depree@gmail.com', $contact_subject, $contact_message, $headers);

    if($acceptedForDelivery)
    {
        echo '<div class="alert alert-success alert-dismissable">';
        echo '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        echo 'Your message was sent. Thank you for contacting me.';
        echo '</div>';
        exit(0);
    }
}

echo '<div class="alert alert-danger alert-dismissable">';
echo '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
echo '<strong>Error!</strong> There was an issue submitting your message.';
echo '</div>';
exit(1);

?>
