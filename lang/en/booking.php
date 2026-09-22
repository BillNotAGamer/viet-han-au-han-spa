<?php

declare(strict_types=1);

return [
    'meta' => [
        'title' => 'Booking',
    ],
    'eyebrow' => 'Appointment request',
    'title' => 'Book a visit',
    'intro' => 'Send your preferred service, date, and time. Our team will contact you to confirm availability before the appointment is final.',
    'process_title' => 'Request first, confirmation after',
    'process_copy' => 'This form records your preferred appointment details. It is not a real-time availability calendar and does not confirm the appointment automatically.',
    'confirmation_title' => 'Manual confirmation',
    'confirmation_copy' => 'A staff member will review your request and contact you by phone to confirm the appointment.',
    'success' => 'Your booking request has been received. The spa will contact you to confirm the appointment.',
    'submit' => 'Send booking request',
    'contact_notice' => 'By submitting this request, you understand that the spa will use the contact information provided to contact you about this booking request.',
    'no_services' => 'Service options are being updated.',
    'fields' => [
        'customer_name' => 'Full name',
        'phone' => 'Phone',
        'service' => 'Interested service',
        'service_placeholder' => 'Choose a service',
        'preferred_date' => 'Preferred date',
        'preferred_time' => 'Preferred time',
    ],
    'validation' => [
        'service_id' => 'Please choose a currently available public service for this language.',
        'preferred_date' => 'Please choose today or a future date.',
    ],
];
