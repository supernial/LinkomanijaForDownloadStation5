# LinkomanijaForDownloadStation5
1.
DownloadStation Plugin for QNAP – Linkomanija Integration
This add-on integrates linkomanija.net into QNAP Download Station (DSv3) as a native search/download provider.

The plugin supports:

Authenticated access to linkomanija.net

Torrent search and retrieval

Direct submission into Download Station

This add-on is built as a signed .addon package

2. Prepare PHP file 
[YOUR USER NAME HERE]
[YOUR PASSWORD HERE]
[PUT YOUR PASS KEY HERE , YOU CAN GET IT FROM GENERATED RRS LINK]
Required Replacements


[YOUR USER NAME HERE]	Your Linkomanija account username
[YOUR PASSWORD HERE]	Your Linkomanija account password
[PUT YOUR PASS KEY HERE , YOU CAN GET IT FROM GENERATED RRS LINK]	


How to Obtain the Passkey

Log in to linkomanija.net.

Navigate to your RSS feed generation page.

Generate a personal RSS link.

Copy the passkey value from the URL.

Paste it into the plugin configuration.

Example RSS format:

https://www.linkomanija.net/rss.php?passkey=XXXXXXXXXXXXXXXXXXXXXXXX

The XXXXXXXXXXXXXXXXXXXXXXXX part is your passkey.

3. Building a Signed Add-on Package

Download Station requires add-ons to be signed using RSA keys and packed with ds-addon-pack.sh.

3.1 Navigate to DSv3 sbin Directory

Typically:

cd /share/MD0_DATA/.qpkg/DSv3/usr/sbin
3.2 Generate RSA Keys (If Not Already Created)
Generate Private Key
/usr/bin/openssl genrsa -out private.pem 1024
Generate Public Key
/usr/bin/openssl rsa -in private.pem -out public.pem -outform PEM -pubout

Verify:

ls -al

You should see:

private.pem
public.pem

These keys are reused for signing future add-ons.

3.3 Pack and Sign the Add-on

Assuming your provider folder is:

addons/linkomanija/

Run:

./ds-addon-pack.sh private.pem public.pem addons/linkomanija

This generates:

linkomanija[version-timestamp].addon

The resulting .addon file will appear inside:

/share/MD0_DATA/.qpkg/DSv3/usr/sbin

4. Verification

After configuration:

Restart Download Station.

Perform a test search or trigger RSS retrieval.

Confirm that torrents are:

Retrieved successfully

Authenticated correctly

Added to the download queue

If authentication fails:

Verify username/password

Verify passkey accuracy

Ensure no extra whitespace characters were inserted

5. Security Notes

Store credentials securely.

Restrict NAS access to trusted users.

Avoid sharing your passkey publicly.

If compromised, regenerate your passkey on linkomanija.net immediately.

6. Compatibility

Tested with QNAP QTS Download Station.

Requires active internet connectivity.

Requires valid Linkomanija account.

If needed, I can also generate:

A README.md version

A formal technical documentation version

A user-friendly end-user version

A developer-focused API documentation version