# RocketCDN Module
This module adds the integration for RocketCDN to WP Rocket.

It includes:
- A ServiceProvider to instantiate all classes via the container
- An APIClient to communicate with RocketCDNAPI
- A CDNOptionsManager to enable/disable CDN options in WP Rocket
- Subscribers to interact with the WordPress API
    - AdminPageSubscriber for actions & filters on WP Rocket settings page
    - DataManagerSubscriber for AJAX actions
    - NoticesSubscriber for all RocketCDN related notices
    - RESTSubscriber for registering the WP REST API routes