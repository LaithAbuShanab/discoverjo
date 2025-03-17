<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute field must be accepted.',
    'accepted_if' => 'The :attribute field must be accepted when :other is :value.',
    'active_url' => 'The :attribute field must be a valid URL.',
    'after' => 'The :attribute field must be a date after :date.',
    'after_or_equal' => 'The :attribute field must be a date after or equal to :date.',
    'alpha' => 'The :attribute field must only contain letters.',
    'alpha_dash' => 'The :attribute field must only contain letters, numbers, dashes, and underscores.',
    'alpha_num' => 'The :attribute field must only contain letters and numbers.',
    'array' => 'The :attribute field must be an array.',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before' => 'The :attribute field must be a date before :date.',
    'before_or_equal' => 'The :attribute field must be a date before or equal to :date.',
    'between' => [
        'array' => 'The :attribute field must have between :min and :max items.',
        'file' => 'The :attribute field must be between :min and :max kilobytes.',
        'numeric' => 'The :attribute field must be between :min and :max.',
        'string' => 'The :attribute field must be between :min and :max characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'can' => 'The :attribute field contains an unauthorized value.',
    'confirmed' => 'The :attribute field confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute field must be a valid date.',
    'date_equals' => 'The :attribute field must be a date equal to :date.',
    'date_format' => 'The :attribute field must match the format :format.',
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => 'The :attribute field must be declined.',
    'declined_if' => 'The :attribute field must be declined when :other is :value.',
    'different' => 'The :attribute field and :other must be different.',
    'digits' => 'The :attribute field must be :digits digits.',
    'digits_between' => 'The :attribute field must be between :min and :max digits.',
    'dimensions' => 'The :attribute field has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'email' => 'The :attribute field must be a valid email address.',
    'ends_with' => 'The :attribute field must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'extensions' => 'The :attribute field must have one of the following extensions: :values.',
    'file' => 'The :attribute field must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute field must have more than :value items.',
        'file' => 'The :attribute field must be greater than :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than :value.',
        'string' => 'The :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute field must have :value items or more.',
        'file' => 'The :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than or equal to :value.',
        'string' => 'The :attribute field must be greater than or equal to :value characters.',
    ],
    'hex_color' => 'The :attribute field must be a valid hexadecimal color.',
    'image' => 'The :attribute field must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field must exist in :other.',
    'integer' => 'The :attribute field must be an integer.',
    'ip' => 'The :attribute field must be a valid IP address.',
    'ipv4' => 'The :attribute field must be a valid IPv4 address.',
    'ipv6' => 'The :attribute field must be a valid IPv6 address.',
    'json' => 'The :attribute field must be a valid JSON string.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'The :attribute field must have less than :value items.',
        'file' => 'The :attribute field must be less than :value kilobytes.',
        'numeric' => 'The :attribute field must be less than :value.',
        'string' => 'The :attribute field must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute field must not have more than :max items.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'The :attribute field must be a file of type: :values.',
    'mimetypes' => 'The :attribute field must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute field must have at least :min items.',
        'file' => 'The :attribute field must be at least :min kilobytes.',
        'numeric' => 'The :attribute field must be at least :min.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute field format is invalid.',
    'numeric' => 'The :attribute field must be a number.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'present_if' => 'The :attribute field must be present when :other is :value.',
    'present_unless' => 'The :attribute field must be present unless :other is :value.',
    'present_with' => 'The :attribute field must be present when :values is present.',
    'present_with_all' => 'The :attribute field must be present when :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute field format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute field must match :other.',
    'size' => [
        'array' => 'The :attribute field must contain :size items.',
        'file' => 'The :attribute field must be :size kilobytes.',
        'numeric' => 'The :attribute field must be :size.',
        'string' => 'The :attribute field must be :size characters.',
    ],
    'starts_with' => 'The :attribute field must start with one of the following: :values.',
    'string' => 'The :attribute field must be a string.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => 'The :attribute field must be a valid URL.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'The :attribute field must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'Name',
        'name-en' => 'English Name',
        'name-ar' => 'Arabic Name',
        'email' => 'Email',
        'password' => 'Password',
        'image' => 'Image',
        'priority' => 'Priority',
        'password_confirmation' => 'Password Confirmation',
        'role' => 'Role',
        'description-en' => 'English Description',
        'description-ar' => 'Arabic Description',
        'address-en' => 'English Address',
        'address-ar' => 'Arabic Address',
        'google-map-url' => 'Google Map URL',
        'phone-number' => 'Phone Number',
        'longitude' => 'Longitude',
        'latitude' => 'Latitude',
        'price-level' => 'Price Level',
        'website' => 'Website',
        'rating' => 'Rating',
        'total-user-rating' => 'Total User Rating',
        'sub_category_id' => 'Subcategory',
        'region-id' => 'Region',
        'business_status' => 'Business Status',
        'tags-id' => 'Tags',
        'main-image' => 'Main Image',
        'gallery-images' => 'Gallery Images',
        'place-type' => 'Place Type',
        'token' => 'Token',
        'subject' => 'Subject',
        'message' => 'Message',
        'description'                               => 'Description',
        'days'                                      => 'Days',
        'days.*.activities'                         => 'Activities',
        'days.*.activities.*.name'                  => 'Activity Name',
        'days.*.activities.*.start_time'            => 'Activity Start Time',
        'days.*.activities.*.end_time'              => 'Activity End Time',
        'days.*.activities.*.place_slug'            => 'Activity Place',
        'days.*.activities.*.note'                  => 'Activity Note',
    ],

    "msg" => [
        "admin-updated-successfully" => 'Admin Updated Successfully!',
        'admin-deleted-successfully' => 'Admin Deleted Successfully!',
        'success' => 'Success',
        'error' => 'Error',
        'delete' => 'Delete',
        'admin-created-successfully' => 'Admin Created Successfully',
        'english-name-required' => 'English name is required.',
        'english-name-min-characters' => 'English name must be at least :min characters.',
        'arabic-name-required' => 'Arabic name is required.',
        'arabic-name-min-characters' => 'Arabic name must be at least :min characters.',
        'priority-required' => 'Priority is required.',
        //        'priority-min-characters' => 'Priority must be at least :min characters.',
        'image-required' => 'Image is required.',
        'image-invalid' => 'Image Invalid',
        'image-mime' => 'The :attribute must be a file of type: :mime_types.',
        'name-required' => 'Name is required.',
        'email-required' => 'Email is required.',
        'email-already-exists' => 'Email already exists.',
        'email-should-be-email-format' => 'Email should be in email format.',
        'password-confirmation-required' => 'Password confirmation is required.',
        'role-required' => 'Role is required.',
        'category-created-successfully!' => 'Category created successfully!',
        'categories-updated-successfully!' => 'Categories Updated Successfully!',
        'categories-deleted-successfully!' => 'Categories Deleted Successfully!',
        'place-created-successfully!' => 'Place Created Successfully!',
        'place-updated-successfully!' => 'Place Updated Successfully!',
        'english-description-required' => 'English Description Is Required',
        'english-description-min-characters' => 'English Description Should Have Minimum Characters',
        'arabic-description-required' => 'Arabic Description Is Required',
        'arabic-description-min-characters' => 'Arabic Description Should Have Minimum Characters',
        'english-address-required' => 'English Address Is Required',
        'english-address-min-characters' => 'English Address Should Have Minimum Characters',
        'arabic-address-required' => 'Arabic Address Is Required',
        'arabic-address-min-characters' => 'Arabic Address Should Have Minimum Characters',
        'google-map-url-required' => 'Google Map URL Is Required',
        'invalid-url' => 'Invalid Google Map URL',
        'phone-number-required' => 'Phone Number Is Required',
        'longitude-required' => 'Longitude Is Required',
        'invalid-longitude' => 'Invalid Longitude',
        'latitude-required' => 'Latitude Is Required',
        'invalid-latitude' => 'Invalid Latitude',
        'price-level-required' => 'Price Level Is Required',
        'website-required' => 'Website Is Required',
        'invalid-website-url' => 'Invalid Website URL',
        'rating-required' => 'Rating Is Required',
        'invalid-rating' => 'Invalid Rating',
        'total-user-rating-required' => 'Total User Rating Is Required',
        'invalid-total-user-rating' => 'Invalid Total User Rating',
        'subcategory-required' => 'Subcategory Is Required',
        'invalid-subcategory' => 'Invalid Subcategory',
        'region-required' => 'Region Is Required',
        'invalid-region' => 'Invalid Region',
        'business-status-required' => 'Business Status Is Required',
        'tags-required' => 'Tags Are Required',
        'invalid-tags' => 'Invalid Tags',
        'invalid-tag' => 'Invalid Tag',
        'main-image-required' => 'Main Image Is Required',
        'invalid-image' => 'Invalid Main Image',
        'invalid-image-format' => 'Invalid Main Image Format',
        'invalid-gallery-image' => 'Invalid Gallery Image',
        'invalid-gallery-image-format' => 'Invalid Gallery Image Format',
        'place-type' => 'Place Type',
        'token-required' => 'Token Is Required',
        'email-valid' => 'Email Is Valid',
        'password-required' => 'Password Is Required',
        'password-confirm' => 'Password Confirmation Is Required',
        'email-invalid' => 'Email Invalid',
        'message-required' => "Message Required",
        'the-note-field-cannot-be-longer-than-255-characters' => 'the note field cannot be longer than 255 characters',
        'the-arabic-note-field-cannot-be-longer-than-255-characters' => 'The Arabic note field cannot be longer than 255 characters',
        'the-name-field-is-required' => 'The name field is required',
        'the-arabic-name-field-is-required' => 'The Arabic name field is required',
        'the-place-field-is-required' => 'The place field is required',
        "question-name-en-required" => "Question English Is Required",
        "question-name-ar-required" => "Question Arabic Is Required",
        "is-first-question-required" => "First Question Is Required",
        'check-if-followers-existence' => 'The user :user not one of your followers',
    ],

    'api' => [
        'relationship_not_exist' => "The relationship :relationship does not exist in User model",
        'place-id-invalid' => 'Place id invalid',
        'this-place-already-in-your-visited-place' => 'This place already in your visited place list',
        'this-place-not-in-your-visited-place-list' => 'This place not in your visited place list to remove it form list',
        'the-selected-comment-id-does-not-exists' => 'The selected Comment id does not exists',
        'the-comment-id-required' => 'The comment id is required',
        'the-content-required' => 'The content is required',
        'you_can_not_delete_the_comment' => 'You can\'t delete the comment',
        'the-status-required' => 'The status like or dislike is required',
        'event-id-is-required' => 'Event id is required',
        'you_are_not_attendance_in_this' => 'You are not an attendee for this trip.',

        'event-id-does-not-exists' => 'Event id does not exists',
        'rating-is-required' => 'Rating field is required',
        'comment-should-be-string' => 'Comment should be string',
        'the-selected-review-id-does-not-exists' => 'The selected review id does not exists',
        'the-review-id-required' => 'The review id is required',
        'the-following-id-is-required' => 'The following id is required',
        'the-following-id-does-not-exists' => 'The following id does not exists',
        'user-id-is-required' => 'user id is required',
        'user-id-does-not-exists' => 'user id does not exists',
        'conversation-id-is-required' => 'Conversation id is required',
        'conversation-id-does-not-exists' => 'Conversation id does not exists',
        'guide-trip-id-required' => 'guide trip id is required',
        'status-is-required' => 'Status is required',
        'guide-trip-user-id-required' => 'Guide trip user id required',
        'guide-trip-user-id-does-not-exists' => 'Guide trip user id does not exists',

        'guide-trip-id-does-not-exists' => 'guide trip id does not exists',
        'media-id-required' => 'media id is required',
        'media-id-does-not-exists' => 'media id does not exists',
        'place-id-does-not-exists' => 'Place id does not exists',
        'this_is_not_in_favorite_list' => 'this is not in favorite list',
        'id-does-not-exists' => ' id does not exists',
        'you-did-not-make-review-for-this' => 'You did not make review for this.',
        'plan-id-does-not-exists' => 'Plan id does not exists',
        'post-id-does-not-exists' => 'Post id does not exists',
        'post-id-invalid' => 'Post id invalid',
        'reply-id-is-required' => 'Reply id is required',
        'reply-id-does-not-exists' => 'Reply id does not exists',
        'content-is-required' => 'Content is required',
        'subcategory-is-required' => 'Subcategory is required',
        'subcategory-does-not-exists' => 'Subcategory does not exists',
        "username-or-email-is-required" => "Username or email is required.",
        "username-or-email-must-be-string" => "Username or email must be a string.",
        "username-or-email-max" => "Username or email must not exceed :max characters.",

        "password-is-required" => "Password is required.",
        'old_password-is-required' => "Old password is required",
        "password-must-comply-with-rules" => "Password must meet the security requirements.",

        "device-token-is-required" => "Device token is required.",
        "device-token-max" => "Device token must not exceed :max characters.",
        // Token
        "token-is-required" => "Token is required.",

        // Email
        "email-is-required" => "Email is required.",
        "email-must-be-valid" => "Email must be a valid email address.",

        // Password
        "password-confirmation-mismatch" => "Password confirmation does not match.",

        // Username
        "username-is-required" => "Username is required.",
        "username-must-be-string" => "Username must be a string.",
        "username-must-be-alpha-dash" => "Username may only contain letters, numbers, dashes, and underscores.",
        "username-min" => "Username must be at least :min characters long.",
        "username-max" => "Username must not exceed :max characters.",
        "username-regex" => "Username must start with a letter and contain only letters, numbers, dashes, or underscores.",
        "username-no-whitespace" => "Username must not contain spaces.",
        "username-unique" => "Username has already been taken.",

        // Email
        "email-must-be-string" => "Email must be a string.",
        "email-must-be-lowercase" => "Email must be in lowercase.",
        "email-max" => "Email must not exceed :max characters.",
        "email-unique" => "Email has already been taken.",
        "email-in-blacklist" => "Email is blacklisted.",

        // Message Type
        "message-type-is-required" => "Message type is required.",
        "message-type-must-be-string" => "Message type must be a string.",
        "message-type-invalid" => "Message type must be one of the following: text, image, audio, or video.",

        // Conversation ID
        "conversation-id-must-exist" => "The selected conversation does not exist.",
        "conversation-id-invalid" => "Invalid conversation ID.",

        // Message Text
        "message-txt-is-required-if-text" => "Message text is required when the message type is text.",
        "message-txt-must-be-string" => "Message text must be a string.",

        // File
        "file-is-required-if-media" => "A file is required when the message type is image, audio, or video.",
        "file-must-be-valid" => "The uploaded file must be a valid file.",
        "file-invalid-type" => "The file must be one of the following types: jpg, jpeg, png, gif, mp3, wav, mp4, mov, avi.",
        "file-max-size" => "The file must not exceed :max kilobytes.",
        // First Name
        "first-name-is-required" => "First name is required.",
        "first-name-must-be-string" => "First name must be a string.",

        // Last Name
        "last-name-is-required" => "Last name is required.",
        "last-name-must-be-string" => "Last name must be a string.",

        // Username
        "username-min-length" => "Username must be at least :min characters.",
        "username-max-length" => "Username must not exceed :max characters.",
        "username-must-match-regex" => "Username must start with a letter and contain only letters, numbers, dashes, and underscores.",
        "username-must-not-contain-spaces" => "Username must not contain spaces.",

        // Birthday
        "birthday-is-required" => "Birthday is required.",
        "birthday-invalid-age" => "You must be at least :min_age years old.",

        // Gender
        "gender-is-required" => "Gender is required.",
        "gender-must-be-valid" => "Gender must be either 1 (male) or 2 (female).",

        // Email

        "email-invalid-format" => "Email must be a valid email address.",
        "email-max-length" => "Email must not exceed :max characters.",

        // Phone Number
        "phone-number-is-required" => "Phone number is required.",
        "phone-number-must-be-string" => "Phone number must be a string.",

        // Description
        "description-is-required" => "Description is required.",
        "description-must-be-string" => "Description must be a string.",

        // Tags ID
        "tags-id-is-required" => "Tags are required.",
        "tags-id-must-exist" => "One or more of the selected tags do not exist.",

        // Image
        "image-is-required" => "Image is required.",
        "image-must-be-an-image" => "The file must be an image.",

        // Password
        "password-confirmed" => "Password confirmation does not match.",

        // Device Token
        "device-token-max-length" => "Device token must not exceed :max characters.",

        // Professional File
        "professional-file-is-required" => "Professional file is required.",
        "professional-file-invalid-format" => "The professional file must be one of the following types: pdf, jpeg, png, jpg, gif, svg, webp, bmp, tiff, ico, svgz.",


        // Name
        "name-is-required" => "Name is required.",

        // Subject (optional)
        "subject-nullable" => "Subject is optional.",

        // Message
        "message-is-required" => "Message is required.",

        // Images
        "images-optional" => "Images are optional.",
        "images-must-be-an-image" => "Each file must be an image.",
        "images-invalid-format" => "Each image must be of type: jpeg, png, jpg, gif, svg, webp, bmp, tiff, ico, svgz.",

        "date-is-required" => "The date is required.",
        "date-invalid-format" => "The date must be a valid date format.",

        // Following ID
        "following-id-is-required" => "The following ID is required.",
        "following-id-not-exists" => "The following user does not exist.",
        "follower-following-already-exists" => "You are already following this user.",


        // Question ID
        "question-id-is-required" => "The question ID is required.",
        "question-id-not-exists" => "The selected question does not exist.",

        // Answer
        "answer-is-required" => "The answer is required.",
        "answer-invalid-value" => "The answer must be 'yes', 'no', or 'I don't know'.",

        // Guide ID
        "guide-id-is-required" => "The guide ID is required.",
        "the-provided-id-not-guide" => 'The provided id not belong to guide',
        "you-can-not-make-rating-for-yourself" => "You can not make rating for yourself",
        "you-made-rating-already" => "You have already made rating for the guide",
        "guide-id-not-exists" => "The selected guide does not exist.",
        "guide-already-joined" => "You have already joined this guide previously.",

        // Rating
        "rating-must-be-numeric" => "The rating must be a numeric value.",
        "rating-min-value" => "The rating must be at least 1.",
        "rating-max-value" => "The rating must not be more than 5.",


        "guide-update-not-allowed" => "You are not allowed to update this guide.",


        // Name
        "name-en-required" => "The English name is required.",
        "name-en-string" => "The English name must be a string.",
        "name-en-max" => "The English name may not be greater than 255 characters.",
        "name-en-check" => "The English name must pass the guide check.",

        "name-ar-required" => "The Arabic name is required.",
        "name-ar-string" => "The Arabic name must be a string.",
        "name-ar-max" => "The Arabic name may not be greater than 255 characters.",

        // Description
        "description-en-required" => "The English description is required.",
        "description-en-string" => "The English description must be a string.",

        "description-ar-required" => "The Arabic description is required.",
        "description-ar-string" => "The Arabic description must be a string.",

        // Main Price
        "main-price-required" => "The main price is required.",
        "main-price-numeric" => "The main price must be a number.",
        "main-price-min" => "The main price must be at least 0.",

        // Date and Time
        "start-datetime-required" => "The start date and time is required.",
        "start-datetime-date" => "The start date and time must be a valid date.",
        "start-datetime-format" => "The start date and time must be in the format Y-m-d H:i:s.",
        "start-datetime-future" => "The start date and time must be a date and time in the future.",

        "end-datetime-required" => "The end date and time is required.",
        "end-datetime-date" => "The end date and time must be a valid date.",
        "end-datetime-format" => "The end date and time must be in the format Y-m-d H:i:s.",
        "end-datetime-future" => "The end date and time must be a date and time in the future.",
        "end-datetime-after-start" => "The end date and time must be after the start date and time.",

        // Max Attendance
        "max-attendance-required" => "The maximum attendance is required.",
        "max-attendance-integer" => "The maximum attendance must be an integer.",
        "max-attendance-min" => "The maximum attendance must be at least 1.",

        // Gallery
        "gallery-file" => "Each gallery item must be a file.",
        "gallery-mimes" => "Each gallery item must be a file of type: jpeg, png, jpg, gif, svg, webp, bmp, tiff, ico, svgz, mp4, mov, avi, mkv, flv, wmv.",

        // Activities
        "activities-required" => "Activities are required.",
        "activities-string" => "Activities must be a string.",

        // Price Include
        "price-include-required" => "Price include is required.",
        "price-include-string" => "Price include must be a string.",

        // Price Age
        "price-age-nullable" => "Price age must be a string if present.",

        // Assembly
        "assembly-required" => "Assembly is required.",
        "assembly-string" => "Assembly must be a string.",

        // Required Items
        "required-items-nullable" => "Required items must be a string if present.",

        // Is Trail
        "is-trail-nullable" => "Is trail must be a boolean if present.",

        // Trail
        "trail-nullable" => "Trail must be a string if present.",

        'guide_trip_id.required' => 'The guide trip ID is required.',
        'guide_trip_id.exists' => 'The selected guide trip ID is invalid.',
        'guide_trip_id.check_if_guide_trip_active_or_in_future' => 'The guide trip must be active or scheduled for the future.',
        'guide_trip_id.check_if_guide_trip_user_exist' => 'The user must exist for the guide trip.',

        'subscribers.required' => 'Subscribers are required.',
        'subscribers.array' => 'Subscribers must be an array.',

        'subscribers.*.first_name.required' => 'The first name for each subscriber is required.',
        'subscribers.*.first_name.string' => 'The first name must be a string.',
        'subscribers.*.first_name.max' => 'The first name may not be greater than 255 characters.',

        'subscribers.*.last_name.required' => 'The last name for each subscriber is required.',
        'subscribers.*.last_name.string' => 'The last name must be a string.',
        'subscribers.*.last_name.max' => 'The last name may not be greater than 255 characters.',

        'subscribers.*.age.required' => 'The age for each subscriber is required.',
        'subscribers.*.age.integer' => 'The age must be an integer.',
        'subscribers.*.age.min' => 'The age must be at least 0.',

        'subscribers.*.phone_number.required' => 'The phone number for each subscriber is required.',
        'subscribers.*.phone_number.string' => 'The phone number must be a string.',
        'subscribers.*.phone_number.max' => 'The phone number may not be greater than 20 characters.',


        'guide-trip-id-invalid' => 'The selected guide trip ID is invalid.',
        'guide-trip-active-or-future' => 'The guide trip must be active or scheduled for the future.',
        'guide-trip-user-joined' => 'The user must have joined the guide trip.',

        'subscribers-required' => 'Subscribers are required.',
        'subscribers-array' => 'Subscribers must be an array.',

        'subscriber-first-name-required' => 'The first name for each subscriber is required.',
        'subscriber-first-name-string' => 'The first name must be a string.',
        'subscriber-first-name-max' => 'The first name may not be greater than 255 characters.',

        'subscriber-last-name-required' => 'The last name for each subscriber is required.',
        'subscriber-last-name-string' => 'The last name must be a string.',
        'subscriber-last-name-max' => 'The last name may not be greater than 255 characters.',

        'subscriber-age-required' => 'The age for each subscriber is required.',
        'subscriber-age-integer' => 'The age must be an integer.',
        'subscriber-age-min' => 'The age must be at least 0.',

        'subscriber-phone-number-required' => 'The phone number for each subscriber is required.',
        'subscriber-phone-number-string' => 'The phone number must be a string.',
        'subscriber-phone-number-max' => 'The phone number may not be greater than 20 characters.',


        'categories-id-nullable' => 'The categories ID field is optional.',
        'categories-id-array' => 'The categories ID field must be an array.',
        'category-exists' => 'One or more categories do not exist.',
        'category-main' => 'The selected category must be a main category.',

        'subcategories-id-nullable' => 'The subcategories ID field is optional.',
        'subcategories-id-array' => 'The subcategories ID field must be an array.',
        'subcategory-exists' => 'One or more subcategories do not exist.',
        'subcategory-main' => 'The selected subcategory must be a valid subcategory.',

        'region-id-nullable' => 'The region ID field is optional.',

        'min-cost-nullable' => 'The minimum cost field is optional.',
        'max-cost-nullable' => 'The maximum cost field is optional.',

        'features-id-nullable' => 'The features ID field is optional.',
        'feature-exists' => 'One or more features do not exist.',

        'min-rate-nullable' => 'The minimum rate field is optional.',
        'max-rate-nullable' => 'The maximum rate field is optional.',
        'category-id-required' => 'The category ID field is required.',
        'category-id-exists' => 'The selected category ID does not exist.',


        'name-required' => 'The name field is required.',
        'name-string' => 'The name must be a string.',
        'name-max' => 'The name may not be greater than 255 characters.',
        'description-required' => 'The description field is required.',
        'description-max' => 'The description may not be greater than 1000 characters.',
        'description-string' => 'The description must be a string.',
        'days-required' => 'The days field is required.',
        'days-array' => 'The days must be an array.',
        'activity-name-required' => 'The activity name field is required.',
        'activity-name-string' => 'The activity name must be a string.',
        'activity-name-max' => 'The activity name may not be greater than 255 characters.',
        'activity-start-time-required' => 'The activity start time field is required.',
        'activity-start-time-format' => 'The activity start time must be in the format H:i.',
        'activity-end-time-required' => 'The activity end time field is required.',
        'activity-end-time-format' => 'The activity end time must be in the format H:i.',
        'activity-note-max' => 'The activity note may not be greater than 255 characters.',

        'day-total-duration' => 'The total duration of activities in a single day cannot exceed 24 hours.',

        'region-id-integer' => 'The region ID must be an integer.',
        'region-id-exists' => 'The selected region ID does not exist.',
        'number-of-days-integer' => 'The number of days must be an integer.',
        'number-of-days-min' => 'The number of days must be at least 1.',

        'plan-id-required' => 'The plan ID is required.',
        'plan-id-exists' => 'The selected plan does not exist or does not belong to you.',

        'activities-array' => 'The activities field must be an array.',

        'activity-end-time-after' => 'The end time must be after the start time.',

        'post-id-required' => 'The post ID is required.',
        'post-id-exists' => 'The selected post does not exist.',
        'post-id-can-make-comment' => 'You cannot comment on this post.',
        'content-required' => 'The content of the comment is required.',
        'content-string' => 'The content must be a string.',

        'visitable-type-required' => 'The visitable type is required.',
        'visitable-type-in' => 'The visitable type must be one of the following: place, plan, trip, event, volunteering.',
        'visitable-id-required' => 'The visitable ID is required.',
        'visitable-id-exists' => 'The selected visitable item does not exist.',

        'privacy-required' => 'Privacy setting is required.',
        'privacy-in' => 'The privacy setting must be one of the following: 0 (public), 1 (private), 2 (friends only).',

        'comment-id-required' => 'The comment ID is required.',
        'comment-id-exists' => 'The selected comment does not exist.',


        'post-id-custom' => 'The post must belong to the user.',

        'visitable-id-custom' => 'The selected visitable does not exist.',

        'latitude-required' => 'The latitude field is required.',
        'latitude-numeric' => 'The latitude must be a number.',
        'longitude-required' => 'The longitude field is required.',
        'longitude-numeric' => 'The longitude must be a number.',


        'gender-required' => 'The gender field is required.',
        'gender-in' => 'The selected gender is invalid.',
        'birthday-required' => 'The birthday field is required.',
        'birthday-min-age' => 'You must be at least 18 years old.',
        'tags-id-required' => 'The tags field is required.',
        'tags-id-exists' => 'One or more of the selected tags do not exist.',
        'username-alpha_dash' => 'The username may only contain letters, numbers, dashes, and underscores.',

        'username-not-regex' => 'The username may not contain spaces.',
        'image-image' => 'The image must be a valid image file.',

        'place_name-required' => 'The place name field is required.',
        'address-required' => 'The address field is required.',
        'images-image' => 'Each image must be a valid image file.',
        'images-mimes' => 'Each image must be one of the following formats: jpeg, png, jpg, gif, svg, webp.',

        'trip_id-required' => 'The trip ID field is required.',
        'trip_id-integer' => 'The trip ID must be an integer.',
        'trip_id-exists' => 'The selected trip ID does not exist.',

        'user_id-required' => 'The user ID field is required.',
        'user_id-integer' => 'The user ID must be an integer.',
        'user_id-exists' => 'The selected user ID does not exist in the users_trips table.',


        'trip_type-required' => 'The trip type field is required.',
        'trip_type-string' => 'The trip type must be a string.',
        'trip_type-in' => 'The selected trip type is invalid.',
        'place_id-required' => 'The place ID field is required.',
        'place_id-integer' => 'The place ID must be an integer.',
        'place_id-exists' => 'The selected place ID does not exist.',
        'cost-required' => 'The cost field is required.',
        'cost-numeric' => 'The cost must be a number.',
        'cost-min' => 'The cost must be at least 0.',
        'age_min-required_if' => 'The age minimum field is required when trip type is 0 or 1.',
        'age_min-integer' => 'The age minimum must be an integer.',
        'age_max-required_if' => 'The age maximum field is required when trip type is 0 or 1.',
        'age_max-integer' => 'The age maximum must be an integer.',
        'date-required' => 'The date field is required.',
        'date-date' => 'The date is not a valid date.',
        'date-custom' => 'The trip date cannot be in the past.',
        'time-required' => 'The time field is required.',
        'time-date_format' => 'The time must be in the format H:i:s.',
        'attendance_number-required_if' => 'The attendance number field is required when trip type is 0 or 1.',
        'attendance_number-integer' => 'The attendance number must be an integer.',
        'attendance_number-min' => 'The attendance number must be at least 1.',
        'tags-required' => 'The tags field is required.',
        'tags-exists' => 'The selected tag does not exist.',
        'users-required_if' => 'The users field is required when trip type is 2.',


        'age_max-gte' => 'The age maximum must be greater than or equal to age minimum.',
        'gender-nullable' => 'The gender field can be null.',

        'date-required_if' => 'The date field is required when time is present.',

        'time-required_if' => 'The time field is required when date is present.',

        'tags-nullable' => 'The tags field can be null.',

        'email-required' => 'The email field is required.',
        'email-string' => 'The email must be a string.',
        'email-email' => 'The email must be a valid email address.',
        'password-required' => 'The password field is required.',
        'password-string' => 'The password must be a string.',

        'lng-required' => 'The longitude field is required.',
        'lat-required' => 'The latitude field is required.',
        'categories-id-invalid' => 'The selected category does not exist or is not a main category.',
        'subcategories-id-invalid' => 'The selected subcategory does not exist or is not a valid subcategory.',



        'email-lowercase' => 'The email must be lowercase.',

        'the-selected-category-does-not-main-category' => 'The selected category does not main category',
        'the-selected-subcategory-it-is-main-category' => 'The selected subcategory it is main category',
        'the-subcategories-should-be-array' => 'The subcategories should be array',
        'the-category-should-be-array' => 'The categories should be array',

        'wrong-email' => 'The provided email is incorrect.',
        'check-update-message-trip' => 'The trip has been updated, please check the trip details.',
        'this-comment-did-not-belong-to-you' => 'This comment does not belong to you.',
        'you-already-make-this-as-favorite' => 'You have already marked this as a favorite.',
        'you-already-make-review-for-this' => 'You have already made a review for this.',
        'you-do-not-have-review' => 'You do not have a review for this.',

        'you_already_make_request_to_this_user_wait_for_accept_id' => 'You have already made a request to this user, wait for acceptance.',
        'you_already_follow_this_user' => 'You are already following this user.',
        'you_can_not_follow_yourself' => 'You cannot follow yourself.',
        'you_are_not_follower_for_this_user' => 'You are not a follower of this user.',
        'you_can_not_unfollow_discover_jordan_profile' => 'You can not unfollow discover Jordan profile',

        'there_is_noting_request_belong_to_this_user_as_follower' => 'There is no request belonging to this user as a follower.',
        'this_user_already_follow_you' => 'This user already follows you.',
        'you_can_not_make_request_to_yourself' => 'You cannot make a request to yourself.',

        'not_owner_of_trip' => 'You are not the owner of this trip.',
        'trip_registration_closed' => 'You can no longer register for this trip.',
        'trip_has_started' => 'The trip started on :start_datetime.',
        'trip_conflict' => 'There is a conflict with your existing trips.',
        'already_in_trip' => 'You are already in this trip.',
        'not_owner_of_image' => 'You are not the owner of this image.',
        'invalid_jordanian_phone_number' => 'The :attribute must be a valid Jordanian phone number.',
        'this_is_not_in_favorite_list_to_delete' => 'This item is not in your favorite list, so it cannot be deleted.',
        'this_place_not_in_your_visited_place_list' => 'This place is not in your visited places list.',
        'you_cannot_make_review_for_upcoming_event' => 'You cannot make a review for an upcoming event.',
        'this_reply_did_not_belong_to_you' => 'This reply does not belong to you.',
        'reply_not_found' => 'The specified reply was not found.',
        'you_cant_make_review_for_upcoming_trip' => 'You can\'t make a review for an upcoming trip.',
        'this-trip-was-deleted' => 'This trip was deleted',
        'comment_not_found' => 'The specified comment was not found.',

        'you_can_not_delete_the_reply' => 'You cannot delete this reply.',
        'you_should_join_trip_first' => 'You should join the trip first.',
        'trip_not_found' => 'The specified trip was not found.',
        'this_trip_inactive' => 'This trip is inactive.',
        'cannot_update_trip_started_at' => 'You cannot update this trip because it started at :date.',
        'you_do_not_have_request_to_delete' => 'You do not have a request to delete this trip.',
        'you_did_not_participate_in_any_of_guide_trip' => 'You did not participate in any of the guide’s trips.',
        'you_already_create_rating_for_this_guide' => 'You have already created a rating for this guide.',
        'you_did_not_make_review_for_this_guide_to_update' => 'You have not made a review for this guide to update.',
        'you_did_not_make_rating_for_this_guide' => 'You have not made a rating for this guide before.',
        'you_should_be_guide_to_create_guide_trip' => 'You must be a guide to create a guide trip.',
        'you_are_not_the_owner_of_this_post' => 'You are not the owner of this post.',
        'invalid_json_format' => 'The provided data is not a valid JSON format.',
        'this_email_in_black_list' => 'This email is in the blacklist.',
        'you-didn\'t-make-this-to-interest-to-delete-interest' => 'You didn\'t mark this as interesting to delete the interest.',
        'you-already-make-this-as-interest' => 'You have already marked this as interesting.',
        'you-can\'t-make-this-as-interest-because-it-in-the-past' => 'You can’t mark this as interesting because it is in the past.',
        'the-current-question-and-next-question-cannot-be-the-same' => 'The current question and next question cannot be the same.',
        'this-conversation-is-not-available' => 'This conversation is not available.',
        'you-are-not-a-member-of-this-conversation' => 'You are not a member of this conversation.',
        'this-post-is-private' => 'This post is private.',
        'you-are-not-following-this-user' => 'You are not following this user.',
        'the-attribute-must-be-at-least-7-years-ago' => 'The :attribute must be at least 7 years ago.',
        'your-attribute-is-required' => 'The :attribute is required.',
        'combination-of-question-answer-and-next-question-must-be-unique' => 'The combination of question, answer, and next question must be unique.',
        'this-name-en-and-location-exists' => 'A place with this name in English and the specified location already exists.',
        'priority-must-be-unique-within-same-parent-category' => 'The priority must be unique within the same parent category.',
        'you-deactivated-by-admin-wait-to-unlock-the-block' => 'You deactivated by admin so wait to unlock the block',
        'the-provided-old-password-is-incorrect' => 'The provided old password is incorrect',
        'you-did-not-join-to-this-trip' => 'you did not join to this trip',
        'invalid-credentials' => 'Invalid credentials',
        'you-should-verify-email-first' => 'You should verify your email first',
        'something-went-wrong' => 'Something went wrong',
        'you_are_not_authorized_to_delete_this_media' => 'You are not authorized to delete this media.',
        'wait-for-admin-to-accept-your-application' => 'Wait for admin to accept your application',

        // Trip Validation
        'trip_type_required'                                        => 'The trip type field is required.',
        'trip_type_integer'                                         => 'The trip type must be an integer.',
        'trip_type_in'                                              => 'The trip type must be one of the following: 0, 1, or 2.',
        'place_slug_required'                                       => 'The place slug field is required.',
        'place_slug_string'                                         => 'The place slug must be a string.',
        'place_slug_exists'                                         => 'The selected place does not exist.',
        'name_required'                                             => 'The name field is required.',
        'name_string'                                               => 'The name must be a string.',
        'name_max'                                                  => 'The name must not exceed 255 characters.',
        'description_required'                                      => 'The description field is required.',
        'description_string'                                        => 'The description must be a string.',
        'cost_required'                                             => 'The cost field is required.',
        'cost_numeric'                                              => 'The cost must be a numeric value.',
        'cost_min'                                                  => 'The cost must be at least 0.',
        'age_min_required_if'                                       => 'The minimum age is required for this trip type.',
        'age_min_integer'                                           => 'The minimum age must be an integer.',
        'age_max_required_if'                                       => 'The maximum age is required for this trip type.',
        'age_max_integer'                                           => 'The maximum age must be an integer.',
        'gender_required'                                           => 'The gender field is required.',
        'date_required'                                             => 'The date field is required.',
        'date_date'                                                 => 'The date must be a valid date.',
        'date_custom'                                               => 'The selected date cannot be in the past.',
        'time_required'                                             => 'The time field is required.',
        'time_date_format'                                          => 'The time must be in the format H:i:s.',
        'attendance_number_required_if'                             => 'The attendance number is required for this trip type.',
        'attendance_number_integer'                                 => 'The attendance number must be an integer.',
        'attendance_number_min'                                     => 'The attendance number must be at least 1.',
        'tags_required'                                             => 'At least one tag is required.',
        'tags_exists'                                               => 'One or more of the selected tags do not exist.',
        'users_required_if'                                         => 'Users are required for this trip type.',
        'the-selected-place-is-not-active'                          => 'The selected place is not active.',
        'cant-make-trip-in-the-same-date-time'                      => 'You can’t create a trip at the same date and time.',
        'time-should-not-be-in-the-past'                            => 'The time should not be in the past.',
        'select_at_least_three_tags'                                => 'Please select at least three tags.',
        'tag_does_not_exist'                                        => 'The tag :tag does not exist.',
        'check_if_followers_existence'                              => 'User :user is not following you.',
        'user_does_not_exist'                                       => 'The user ":user" does not exist.',
        'date-cannot-be-in-the-past'                                => 'The date cannot be in the past.',
        'this_user_has_joined_a_trip_on_the_same_date_as_your_trip' => 'This user has joined a trip on the same date as your trip, so he cannot join your trip.',
        'this-user-has-already-joined-this-trip'                    => 'This user has already joined this trip.',
        'this-user-has-been-rejected-from-this-trip'                => 'This user has been rejected from this trip.',
        'this-user-has-left-this-trip'                              => 'This user has left this trip.',
        'trip_slug_required'                                        => 'The trip identifier (slug) is required.',
        'trip_slug_exists'                                          => 'The selected trip does not exist.',
        'you-should-enter-your-birthday-first'                      => 'You should enter your birthday first.',
        'this-trip-has-exceeded-the-required-number'                => 'This trip has exceeded the required number. You can return to the homepage and search for another trip.',
        'this-journey-has-already-moved-on'                         => 'This journey has already moved on. You can return to the homepage and search for another trip.',
        'age-or-sex-not-acceptable'                                 => 'You are not allowed to join this trip because your age or sex is not acceptable.',
        'join-request-cancelled-by-owner'                           => 'Your join request was cancelled by the owner, so you cannot join this trip again.',
        'already-joined-this-trip'                                  => 'You have already joined this trip.',
        'creator-cannot-join-trip'                                  => 'You are the creator of this trip, so you cannot join it.',
        'already-joined-another-trip-on-same-date'                  => 'You have already joined another trip on the same date.',
        'you-are-owner-of-trip'                                     => 'You are the owner of this trip, so you can\'t cancel the join.',
        'you-didnt-join-trip'                                       => 'You didn\'t join this trip to cancel.',
        'trip-already-canceled'                                     => 'The owner of the trip has already canceled your join.',
        'you-left-trip'                                             => 'You have already left this trip.',
        'you-are-not-owner-of-trip'                                 => 'You are not the owner of this trip.',
        'age_max_gte'                                               => 'The maximum age must be greater than or equal to the minimum age.',
        'gender_nullable'                                           => 'The gender field is optional.',
        'date_required_if'                                          => 'The date is required when the time is provided.',
        'date_after_or_equal'                                       => 'The date must be in the future.',
        'time_required_if'                                          => 'The time is required when the date is provided.',
        'tags_nullable'                                             => 'Tags are optional.',
        'date_invalid'                                              => 'The date must be a valid date.',
        'date_must_be_future'                                       => 'The date must be in the future.',
        'cant-make-trip-in-the-same-date-time'                      => 'You can’t create a trip at the same date and time.',
        'cant-make-trip-in-this-date-you-already-on-trip'           => 'You can’t create a trip on this date because you are already on another trip.',
        'status_required'                                           => 'The status field is required.',
        'status_invalid'                                            => 'The status must be either "accept" or "cancel".',
        'trip_slug_string'                                          => 'The trip identifier must be a string.',
        'the-user-is-not-a-member-of-this-trip'                     => 'The user is not a member of this trip.',
        'user_slug_required'                                        => 'The user identifier (slug) is required.',
        'user_slug_exists'                                          => 'The selected user does not exist.',

        // Plan Validation
        'plan_slug_plan_error_main'                           => 'The plan identifier (slug) is required Or the plan does not exist.',
        'name_plan_error_main'                                => 'The plan name is required or must be a string with a maximum of 255 characters.',
        'description_plan_error_main'                         => 'The plan description is required or must be a string with a maximum of 1000 characters.',
        'days_plan_error'                                     => 'The days for the plan is required or must be an array.',
        'activities_plan_error'                               => 'The activities for day :day is required or must be an array.',
        'name_plan_error'                                     => 'The plan name for day :day, activity :activity is required or must be a string with a maximum of 255 characters.',
        'start_time_plan_error'                               => 'The start time for day :day, activity :activity is required or must be in the format H:i.',
        'end_time_plan_error'                                 => 'The end time for day :day, activity :activity is required or must be in the format H:i.',
        'place_slug_plan_error'                               => 'The selected place for day :day, activity :activity is required or does not exist.',
        'note_plan_error'                                     => 'The note for day :day, activity :activity must be a string with a maximum of 255 characters.',
        'start_time_custom_plan_error'                        => 'The start time for day :day, activity :activity must be in sequential order.',
        'end_time_custom_plan_error'                          => 'The end time for day :day, activity :activity must be after the start time.',
        'plan-slug-invalid'                                   => 'The plan slug is invalid.',
        'plan-slug-does-not-exists'                           => 'The plan does not exist.',
        'you_are_not_the_owner_of_this_plan'                  => 'You are not the owner of this plan',

        // Category Validation
        'the-category-does-not-exists'                        => 'The category does not exists',
        'the-selected-category-does-not-main-category'        => 'The selected category does not main category',
        'the-categories-should-be-array'                      => 'The categories should be an array',
        'the-selected-category-id-does-not-exists'            => 'The selected category does not exist.',
        'the-category-id-required'                            => 'The category ID is required.',
        'invalid-category-id-not-main-category'               => 'Invalid category Id, it is not main category',

    ]

];
