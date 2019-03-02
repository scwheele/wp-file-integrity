# wp-file-integrity

Scans WordPress filesystem to determine any changes made to the files.

## Configuration

### Email
Email notification will be sent to the address configured in General Settings.

Go to Settings > General > WP Sec Configuration

![alt text](https://i.imgur.com/5NVTzo3.png "Settings > General > WP Sec Configuration")

## View Logs
Logs will report the most recent 50 file changes, however a full change report will be available by email.

![alt text](https://i.imgur.com/FrV7Qbx.png "File Change Logs")

## Updates

### Known Issues


### Fixed
- Fixed issue where cron was not triggering
- Fixed issue where cron & database was not being removed on uninstall
- May send emails at random intervals, even if no changes were detected

### TODO

- Add dashboard to view general logging
- Add configuration tool to determine what is logged
- Add user registration logging
- Add logging for when themes are updated by the WP editor