=== EA WP AWS SNS Client REST Endpoint ===
Contributors: bbrian
Donate link: https://BrianHenry.ie
Tags: aws, sns
Requires at least: 5.1.1
Tested up to: 5.1.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a REST endpoint to WordPress for receiving AWS SNS messages; fires `do_action( 'ea_aws_sns_notification' ...` for plugins to consume; uses `admin_notice` to confirm new subscriptions.

== Description ==

https://aws.amazon.com/sns/

== Installation ==

1. Upload `ea-wp-aws-sns-client-rest-endpoint.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place the following in your plugin:

```
add_filter( 'ea_aws_sns_notification', 'my_notification_handler', 10, 5 );

/*
 * @param array   $handled                 An array of plugins that have handled the notification.
 * @param string  $notification_topic_arn  $body->TopicArn
 * @param array   $headers                 HTTP headers received
 * @param object  $body                    HTTP body received
 * @param object  $message                 $body->Message JSON decoded
 */
function my_notification_handler( $handled $notification_topic_arn, $headers, $body, $message ) {

	$my_topic_arn = ...

	if( $my_topic_arn != $notification_topic_arn ) {
		return $handled;
	}

	// Handle

    ...

    // Confirm the notification has been handled.
	$handled[] = array( 'my-plugin-name', __FUNCTION__ );

	return $handled;
}
```

== Changelog ==

= 2.0.0 =

Changed action to filter so this plugin can log if the notification was handled.

= 1.0.0 =
* Adequately working internally.