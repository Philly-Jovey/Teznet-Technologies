# Volunteer application deployment

1. Create the application table by running `volunteer-applications.sql` against the MySQL database used by the site.
2. Configure the PHP environment variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` with a database account permitted to insert application rows.
3. Set `TEZNET_APPLICATION_UPLOAD_DIR` to a writable directory outside the public website document root. The PHP process sets this directory to mode `0700` and uploaded files to `0600`. Grant filesystem access only to the web process and staff who are authorized to review applications. Do not expose the directory through a web server alias.
4. Configure PHP with `file_uploads=On`, `upload_max_filesize=3M`, `post_max_size=14M`, and `max_file_uploads` of at least `4`. Enable the Fileinfo extension and ZipArchive for server-side file checks. Confirm the web server request body limit is at least 14 MiB.
5. Serve the form and submit handler over HTTPS. Back up the private document directory and database using the same staff access controls.

Applications remain unavailable with an HTTP 503 response until the database and private storage settings are present. Uploaded filenames are replaced with random names; the original filename is kept only as application metadata.
