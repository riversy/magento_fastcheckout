Validation.addAllThese([
    ['validate-phoneCheckout', 'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.', function(v) {
                return Validation.get('IsEmpty').test(v) || /^[0-9\s()+-]{4,20}$/.test(v);
            }]
]);

