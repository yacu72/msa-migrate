<?php

/**
 * Migrate clients tickets
 *
 * We use functions that existis in buddypress plugin.
 */
if ( ! class_exists( 'Msa_Migrate_tickets' ) ) :
	class Msa_Migrate_tickets {

		function __construct() {

    }


	/**
	 * Create a new message. cloned function, mail notification removed
	 *
	 * @since 2.4.0 Added 'error_type' as an additional $args parameter.
	 *
	 * @param array|string $args {
	 *     Array of arguments.
	 *     @type int    $sender_id  Optional. ID of the user who is sending the
	 *                              message. Default: ID of the logged-in user.
	 *     @type int    $thread_id  Optional. ID of the parent thread. Leave blank to
	 *                              create a new thread for the message.
	 *     @type array  $recipients IDs or usernames of message recipients. If this
	 *                              is an existing thread, it is unnecessary to pass a $recipients
	 *                              argument - existing thread recipients will be assumed.
	 *     @type string $subject    Optional. Subject line for the message. For
	 *                              existing threads, the existing subject will be used. For new
	 *                              threads, 'No Subject' will be used if no $subject is provided.
	 *     @type string $content    Content of the message. Cannot be empty.
	 *     @type string $date_sent  Date sent, in 'Y-m-d H:i:s' format. Default: current date/time.
	 *     @type string $error_type Optional. Error type. Either 'bool' or 'wp_error'. Default: 'bool'.
	 * }
	 *
	 * @return int|bool|WP_Error ID of the message thread on success, false on failure.
	 */
	public function msa_migrate_ticktes_new_ticket( $args = '' ) {

		// Parse the default arguments.
		$r = bp_parse_args( $args, array(
			'sender_id'  => bp_loggedin_user_id(),
			'thread_id'  => false,   // False for a new message, thread id for a reply to a thread.
			'recipients' => array(), // Can be an array of usernames, user_ids or mixed.
			'subject'    => false,
			'content'    => false,
			'date_sent'  => bp_core_current_time(),
			'error_type' => 'bool'
		), 'msa_migrate_ticktes_new_ticket' );

		// Bail if no sender or no content.
		if ( empty( $r['sender_id'] ) || empty( $r['content'] ) ) {
			if ( 'wp_error' === $r['error_type'] ) {
				if ( empty( $r['sender_id'] ) ) {
					$error_code = 'messages_empty_sender';
					$feedback   = __( 'Your message was not sent. Please use a valid sender.', 'buddypress' );
				} else {
					$error_code = 'messages_empty_content';
					$feedback   = __( 'Your message was not sent. Please enter some content.', 'buddypress' );
				}

				return new WP_Error( $error_code, $feedback );

			} else {
				return false;
			}
		}

		// Create a new message object.
		$message            = new BP_Messages_Message;
		$message->thread_id = $r['thread_id'];
		$message->sender_id = $r['sender_id'];
		$message->subject   = $r['subject'];
		$message->message   = $r['content'];
		$message->date_sent = $r['date_sent'];

		// If we have a thread ID...
		if ( ! empty( $r['thread_id'] ) ) {

			// ...use the existing recipients
			$thread              = new BP_Messages_Thread( $r['thread_id'] );
			$message->recipients = $thread->get_recipients();

			// Strip the sender from the recipient list, and unset them if they are
			// not alone. If they are alone, let them talk to themselves.
			if ( isset( $message->recipients[ $r['sender_id'] ] ) && ( count( $message->recipients ) > 1 ) ) {
				unset( $message->recipients[ $r['sender_id'] ] );
			}

			// Set a default reply subject if none was sent.
			if ( empty( $message->subject ) ) {
				$message->subject = sprintf( __( 'Re: %s', 'buddypress' ), $thread->messages[0]->subject );
			}

		// ...otherwise use the recipients passed
		} else {

			// Bail if no recipients.
			if ( empty( $r['recipients'] ) ) {
				if ( 'wp_error' === $r['error_type'] ) {
					return new WP_Error( 'message_empty_recipients', __( 'Message could not be sent. Please enter a recipient.', 'buddypress' ) );
				} else {
					return false;
				}
			}

			// Set a default subject if none exists.
			if ( empty( $message->subject ) ) {
				$message->subject = __( 'No Subject', 'buddypress' );
			}

			// Setup the recipients array.
			$recipient_ids = array();

			// Invalid recipients are added to an array, for future enhancements.
			$invalid_recipients = array();

			// Loop the recipients and convert all usernames to user_ids where needed.
			foreach ( (array) $r['recipients'] as $recipient ) {

				// Trim spaces and skip if empty.
				$recipient = trim( $recipient );
				if ( empty( $recipient ) ) {
					continue;
				}

				// Check user_login / nicename columns first
				// @see http://buddypress.trac.wordpress.org/ticket/5151.
				if ( bp_is_username_compatibility_mode() ) {
					$recipient_id = bp_core_get_userid( urldecode( $recipient ) );
				} else {
					$recipient_id = bp_core_get_userid_from_nicename( $recipient );
				}

				// Check against user ID column if no match and if passed recipient is numeric.
				if ( empty( $recipient_id ) && is_numeric( $recipient ) ) {
					if ( bp_core_get_core_userdata( (int) $recipient ) ) {
						$recipient_id = (int) $recipient;
					}
				}

				// Decide which group to add this recipient to.
				if ( empty( $recipient_id ) ) {
					$invalid_recipients[] = $recipient;
				} else {
					$recipient_ids[] = (int) $recipient_id;
				}
			}

			// Strip the sender from the recipient list, and unset them if they are
			// not alone. If they are alone, let them talk to themselves.
			$self_send = array_search( $r['sender_id'], $recipient_ids );
			if ( ! empty( $self_send ) && ( count( $recipient_ids ) > 1 ) ) {
				unset( $recipient_ids[ $self_send ] );
			}

			// Remove duplicates & bail if no recipients.
			$recipient_ids = array_unique( $recipient_ids );
			if ( empty( $recipient_ids ) ) {
				if ( 'wp_error' === $r['error_type'] ) {
					return new WP_Error( 'message_invalid_recipients', __( 'Message could not be sent because you have entered an invalid username. Please try again.', 'buddypress' ) );
				} else {
					return false;
				}
			}

			// Format this to match existing recipients.
			foreach ( (array) $recipient_ids as $i => $recipient_id ) {
				$message->recipients[ $i ]          = new stdClass;
				$message->recipients[ $i ]->user_id = $recipient_id;
			}
		}

		// Bail if message failed to send.
		$send = $message->send();
		if ( false === is_int( $send ) ) {
			if ( 'wp_error' === $r['error_type'] ) {
				if ( is_wp_error( $send ) ) {
					return $send;
				} else {
					return new WP_Error( 'message_generic_error', __( 'Message was not sent. Please try again.', 'buddypress' ) );
				}
			}

			return false;
		}


		// Return the thread ID.
		return $message->thread_id;
	}    

	}
endif;