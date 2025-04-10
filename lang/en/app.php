<?php

return [
    //translation email verification
    "verifyEmail" => "Verify your email",
    "logo" => "Logo",
    "verifyEmailMessage" => "You have requested to verify your email. Please click the button below to verify your email:",
    "verifyEmailButton" => "Verify Email",
    "thankYou" => "If you did not request email verification, you can ignore this email.",
    "allReserved" => "All rights reserved to Discover Jo",
    "yourNotVerified" => "Your email has not been verified. Please activate your account before logging in.",
    //translation email reset password
    'discover-jordan-logo' => 'Discover Jordan logo',
    'discover-jordan' => 'Discover Jordan',
    'reset_password_notification' => 'Password reset notification',
    'received_password_reset_request' => 'You’re receiving this email because we received a password reset request for your account.',
    'reset_password' => 'Reset your password',
    'password_reset_link_expire' => 'This password reset link will expire in :count minutes.',
    'no_further_action_required' => 'If you didn’t request a password reset, no further action is required.',
    'all-rights-reserved' => 'All rights reserved.',
    'all-copy-right-reserved-by-discover-jordan' => 'All rights reserved by Discover Jordan.',
    'reset_password_title' => 'Discover Jordan | Reset your password',
    'reset_password_description' => 'Please complete the following fields to reset your password.',
    'reset-button' => 'Reset password',
    'passwords_do_not_match' => 'Passwords do not match.',
    'something_went_wrong' => 'Oops! Something went wrong. Please try again.',
    'password_reset_successfully' => 'Your password has been successfully reset. You can now log in with your new credentials.',

    // fields
    'email' => 'Email',
    'username' => 'Username',
    'enter-username' => 'Please enter your username',
    'enter-email' => 'Please enter your email',
    'enter-password' => 'Please enter your password',
    'password' => 'Password',
    'confirm-password' => 'Confirm password',
    'enter-confirm-password' => 'Please confirm your password',


    'event' => [
        'api' => [
            'you-add-this-event-to-interest-successfully' => 'You added this event to your interests successfully',
            'you-already-make-this-as-interest' => 'You have already marked this as an interest',
            'you-can\'t-make-this-as-interest-because-it-in-the-past' => 'You can\'t mark this as an interest because it\'s in the past',
            'you-didn\'t-make-this-to-interest-to-delete-interest' => 'You haven\'t marked this as an interest to delete it',
            'you-remove-this-event-from-interested-list' => 'You removed this event from your interests successfully',
            'you-already-make-this-as-favorite' => 'You have already marked this as a favorite',
            'you-added-event-in-favorite-successfully' => 'You added this event to your favorites successfully',
            'this-is-not-in-favorite-list-to-delete-it-from-list' => 'This event is not in your favorites list to delete it',
            'you-remove-event-from-favorite-successfully' => 'You removed this event from your favorites successfully',
            'you-already-make-review-for-this' => 'You have already made a review for this event',
            'you-can\'t-make-review-for-upcoming' => 'You can\'t make a review for an upcoming event',
            'you-added-review-for-this-event-successfully' => 'You added a review for this event successfully',
            'you-do-not-have-review' => 'You don\'t have a review to update',
            'you-update-your-review-successfully' => 'You updated your review successfully',
            'you-deleted-your-review-successfully' => 'You deleted your review successfully',
            'the-likable-status-change-successfully' => 'The likable status was changed successfully',
            'user-details-retrieved-successfully' => 'User details retrieved successfully',
            'the-interest-event-retrieved-successfully' => 'The interest event list retrieved successfully',

        ],
    ],

    'place' => [
        'api' => [
            'you-put-this-in-favorite-list' => 'You put this in favorite list successfully',
            'you-delete-this-from-favorite-list' => 'You deleted this form favorite list.',
            'you-remove-this-place-in-favorite-list' => 'You remove this place in favorite list successfully',
            'you-put-this-place-in-visited-place-list' => 'You put this place in visited place list',
            'remove-place-form-visited-places-list-successfully' => 'You remove this place from visited place successfully',
            'you-added-review-for-this-place-successfully' => 'You added review for this place successfully',
            'you-updated-review-for-this-place-successfully' => 'You updated review for this place successfully',
            'you-remove-review-for-this-place-successfully' => 'You remove review for this place successfully',
        ],

    ],

    "plan" => [
        'api' => [
            'favorite-plan-created-successfully' => 'You added this plan to favorite list',
            'you-remove-plan-from-favorite-list' => 'You removed this plan from favorite list',
            'the-likable-status-change-successfully' => 'The likable status changed successfully',
        ]
    ],

    "notifications" => [

        // Trip Requests
        "new-request"                                   => "New Request In Your Trip",
        "new-user-request-from-trip"                    => "User :username has sent a request to join your trip",
        "accepted-trip"                                 => "You have been accepted into the trip",
        "rejected-trip"                                 => "You have been rejected into the trip",
        "accepted-trip-body"                            => ":username has accepted your request for the trip :trip_name",
        "rejected-trip-body"                            => ":username has rejected your request for the trip :trip_name",

        // Trip Creation & Invitations
        "new-trip-title"                                => "There is a new trip",
        "new-trip-body"                                 => "The :username has created a new trip",
        'new-trip-invitation-title'                     => 'New Trip Invitation',
        'new-trip-invitation-body'                      => ':username has invited you to join a trip.',
        "accepted-invitation-trip"                      => "Invitation Accepted",
        "rejected-invitation-trip"                      => "Invitation Rejected",
        "accepted-invitation-trip-body"                 => ":username has accepted the invitation",
        "rejected-invitation-trip-body"                 => ":username has rejected the invitation",

        // Posts
        'new-post-title'                                => 'New Post Alert!',
        'new-post-body'                                 => 'Exciting news! :username has just shared a new post. Stay updated and check it out!',
        'new-post-like'                                 => 'New Like',
        'new-user-like-in-post'                         => 'The User :username has liked your post',
        'new-post-dislike'                              => 'New Dislike',
        'new-user-dislike-in-post'                      => 'The User :username has disliked your post',

        // Comments
        'new-comment'                                   => 'There is a new comment',
        'new-user-comment-in-post'                      => 'The User :username has added a new comment',
        'new-comment-like'                              => 'New Like',
        'new-user-like-in-comment'                      => 'The User :username has liked your comment',
        'new-comment-dislike'                           => 'New Dislike',
        'new-user-dislike-in-comment'                   => 'The User :username has disliked your comment',

        // Warnings & Account Status
        'new-warning-title'                             => 'New Warning',
        'new-warning-body'                              => 'You have received a warning due to unethical behavior. Please follow our community guidelines.',
        'new-blocked-two-weeks-title'                   => 'Account Temporarily Blocked',
        'new-blocked-two-weeks-body'                    => 'Your account has been blocked for two weeks due to repeated violations of our policies.',
        'new-blacklisted-title'                         => 'Account Blacklisted',
        'new-blacklisted-body'                          => 'Your account has been permanently blacklisted due to severe violations of our policies.',

        // Reviews
        'new-review'                                    => 'New Review',
        'new-user-review-in-trip'                       => 'The User :username has added a review to your trip',
        'new-user-review-in-guideTrip'                  => 'The User :username has added a review to your guided trip',
        'new-review-like'                               => 'New Like',
        'new-user-like-in-review'                       => 'The User :username has liked your review',
        'new-review-dislike'                            => 'New Dislike',
        'new-user-dislike-in-review'                    => 'The User :username has disliked your review',

        // Following
        'new-following-request'                         => 'New following request',
        'new-user-following-request'                    => 'The User :username has sent you a following request',
        'accept-your-following-request'                 => 'Acceptance of Following Request',
        'the-following-accept-your-following-request'   => 'The User :username has accepted your following request',

        // Messaging
        'new-message'                                   => 'New Message',
        'new-user-message'                              => 'The User :username has sent you a new message',

        // Gide Trips
        'accepted-guide-trip-title'                     => ':username was accepted on the trip.',
        'accepted-guide-trip-body'                      => ':username was accepted on the trip :trip_name.',
        'declined-guide-trip-title'                     => ':username was rejected on the trip.',
        'declined-guide-trip-body'                      => ':username was rejected on the trip :trip_name.',
    ],


    "api" => [
        /** for categories */
        'comment-created-successfully' => 'Comment created successfully',
        'comment-updated-successfully' => 'Comment updated successfully',
        'comment-deleted-successfully' => 'Comment deleted successfully',
        'the-following-request-sent-successfully' => 'The following request sent successfully',
        'follows-deleted-successfully' => 'Following deleted successfully',
        'accept-follow-request-successfully' => 'Your following request accepted successfully',
        'un-accept-follow-request-successfully' => 'Unaccepted follow request successfully',
        'followers-requests-retrieved-successfully' => 'Followers request retrieved successfully',
        'followers-retrieved-successfully' => 'Followers retrieved successfully',
        'followings-retrieved-successfully' => 'Followings retrieved successfully',
        'first-question-retrieved-successfully' => 'First question retrieved successfully',
        'next-question-retrieved-successfully' => 'Next question retrieved successfully',
        'the-result-retrieved-successfully' => 'The result retrieved successfully',
        'rating-created-successfully' => 'Your rating created successfully',
        'rating-updated-successfully' => 'Your rating updated successfully',
        'rating-deleted-successfully' => 'Your rating deleted successfully',
        'guide-trips-retrieved-successfully' => 'Guides trips retrieved successfully',
        'trip-retrieved-successfully' => 'Trip retrieved successfully',
        'trip-created-successfully' => 'Trip created successfully',
        'trip-updated-successfully' => 'Trip updated successfully',
        'trip-deleted-successfully' => 'Trip deleted successfully',
        'trip-image-deleted-successfully' => 'Trip\'s image deleted successfully',
        'join-requests-retrieved-successfully' => 'Join requests retrieved successfully',
        'join-requests-status-changed-successfully' => 'Status of join request changed successfully',
        'guide-trips-subscription-created-successfully' => 'Your request for attendance created successfully',
        'guide-trips-users-subscription-updated-successfully' => 'Your request for attendance updated successfully',
        'guide-trips-users-subscription-deleted-successfully' => 'Your request for attendance deleted successfully',
        'you-added-trip-in-favorite-successfully' => 'You added this trip to favorite successfully',
        'you-deleted-the-trip-from-favorite-Successfully' => 'You deleted this trip from favorite list successfully',
        'you-added-review-for-this-trip-successfully' => 'You added review for this trip successfully',
        'your-review-in-this-trip-updated-successfully' => 'Yor review in this trip updated successfully',
        'your-review-updated-successfully' => 'Yor review updated successfully',
        'you-deleted-your-review-trip-successfully' => 'Your review on this trip deleted successfully',
        'the-plan-retrieved-successfully' => 'The plan retrieved successfully',
        'plan-deleted-successfully' => 'Plan deleted successfully',
        'post-retrieved-successfully' => 'Post retrieved successfully',
        'post-created-successfully' => 'Post created successfully',
        'post-updated-successfully' => 'Post updated successfully',
        'favorite-post-created-successfully' => 'Post added to favorite list successfully',
        'favorite-post-deleted-successfully' => 'Post removed for favorite list successfully',
        'reply-created-successfully' => 'Reply created successfully',
        'reply-updated-successfully' => 'Reply updated successfully',
        'reply-deleted-successfully' => 'Reply deleted successfully',
        'onboardings-retrieved-successfully' => 'Onboardings retrieved successfully',
        'user-details-retrieved-successfully' => 'User details retrieved successfully',
        'your-profile-updated-successfully' => 'Your profile updated successfully',
        'your-location-set-successfully' => 'Your location sat successfully',
        'your-all-favorite-retrieved-successfully' => 'Your favorite retrieved successfully',
        'the-users-retried-successfully' => 'The users retrieved successfully',
        'the-searched-favorite-retrieved-successfully' => 'the searched favorite retrieved successfully',
        'all-tags-retrieved-successfully' => 'All tags retrieved successfully',

        // Auth Api
        'you-logged-in-successfully'                                   => 'You logged in successfully',
        'you-register-successfully'                                    => 'Congratulations! Your registration was successful. Enjoy your journey with us.',
        'you-logged-out-successfully'                                  => 'Successfully logged out. We hope to see you again soon. Have a great time!',
        'your-account-deleted-successfully'                            => 'Your account deleted successfully!',
        'your-account-deactivated-successfully'                        => 'Your account deactivated successfully!',
        'email-already-verified'                                       => 'Your Email already verified',
        "email-sent-successfully"                                      => "Email Sent Successfully",
        'the-link-for-reset-password-sent-successfully'                => 'The link for reset password sent successfully',
        'unable-to-send-the-link-for-reset-password'                   => 'Unable to send the link for reset password',
        'your-password-reset-successfully'                             => 'Your password has been reset successfully! You can now log in with your new password.',
        'unable-to-reset-your-password'                                => 'Unable to reset your password',
        'an-error-occurred-while-resetting-the-password'               => 'An error occurred while resetting the password',
        'you-have-already-verify-your-email'                           => 'You have already verify your email',
        'you-have-verify-your-email-successfully'                      => 'your email verified successfully',

        // Trip Api
        'retrieved-successfully'                                       => 'Data recovered successfully',
        'trip-created-successfully'                                    => 'Trip created successfully',
        'the-status-change-successfully'                               => 'The status change successfully',
        'you-join-to-trip-successfully'                                => 'You join to trip successfully',
        'you-are-left-from-the-trip-successfully'                      => 'You are left from the trip successfully',
        'the-trip-deleted-successfully'                                => 'The trip deleted successfully',
        'the-trip-updated-successfully'                                => 'The trip updated successfully',
        'the-user-deleted-successfully'                                => 'The user deleted successfully',
        'trips-retrieved-successfully'                                 => 'Trips retrieved successfully',
        'the-searched-trip-retrieved-successfully'                     => 'The searched trip retrieved successfully',

        // Plan Api
        'plan-created-successfully'                                    => 'Plan created successfully',
        'plan-updated-successfully'                                    => 'Plan updated successfully',
        'plan-deleted-successfully'                                    => 'Plan deleted successfully',
        'plans-retrieved-successfully'                                 => 'Plans retrieved successfully',
        'the-searched-plan-retrieved-successfully'                     => 'The searched plan retrieved successfully',

        // Category Api
        'categories-retrieved-successfully'                            => 'Categories retrieved successfully',
        'all-subcategories-retrieved-successfully'                     => 'All subcategories retrieved successfully',
        'places-subcategories-retrieved-successfully'                  => 'Places and SubCategories by Category id Retrieved Successfully.',
        'the-searched-categories-retrieved-successfully'               => 'The searched categories retrieved successfully.',

        // Place Api
        'place-retrieved-by-id-successfully'                           => 'Place retrieved by id successfully',
        'the-searched-place-retrieved-successfully'                    => 'The searched place retrieved successfully',

        // SubCategory Api
        'places-of-subcategories-retrieved-successfully'               => 'Places of SubCategories retrieved successfully',
        'this-is-main-category'                                        => 'This is main category you need subcategory',

        // TopTen Api
        'top-ten-places-retrieved-successfully'                        => 'Top ten places retrieved successfully',
        'searched-top-ten-places-retrieved-successfully'               => 'Searched top ten places retrieved successfully',

        // Popular Api
        'popular-places-retrieved-successfully'                        => 'Popular places retrieved successfully',
        'the-searched-places-retrieved-successfully'                   => 'The searched places retrieved successfully',

        // Event Api
        'events-retrieved-successfully'                                => 'Events retrieved successfully',
        'active-events-retrieved-successfully'                         => 'Active events retrieved successfully',
        'event-retrieved-successfully'                                 => 'Event retrieved successfully',
        'events-of-specific-date-retrieved-successfully'               => 'Events of specific date retrieved successfully',
        'the-searched-event-retrieved-successfully'                    => 'The searched event was retrieved successfully',

        // Volunteering Api
        'volunteering-retrieved-successfully'                          => 'Volunteering retrieved successfully',
        'active-volunteering-retrieved-successfully'                   => 'Active volunteering retrieved successfully',
        'volunteering-of-specific-date-retrieved-successfully'         => 'Volunteering of specific date retrieved successfully',
        'the-searched-volunteering-retrieved-successfully'             => 'The searched volunteering was retrieved successfully',

        // Legal Api
        'legal-retrieved-successfully'                                 => 'Legal retrieved successfully',

        // Contact Api
        'contact-us-created-successfully'                              => 'Contact us created successfully',

        // Suggestion Api
        'suggestion-place-created-successfully'                        => 'Suggestion place created successfully',

        // Guide Trip Api
        'the-searched-guide-trip-retrieved-successfully'               => 'The searched guide trip retrieved successfully',

        // User Api
        'the-users-retried-successfully'                               => 'The users retried successfully',
        'your-place-current-location-retrieved-successfully'            => 'Your place current location retrieved successfully',
    ],
];
