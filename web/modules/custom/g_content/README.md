## G Content

G Content populates a site from a Google Docs spreadsheet.
Currently nodes are supported but I hope to add support for redirects and taxononmies further down the track.

## Installation

<code>drush pm-enable g_content</code>

## Configuration

Add the url to your spreadsheets using the format below(the spreadsheet needs to be public):
<code>https://spreadsheets.google.com/feeds/list/<KEY>/<SHEET>/public/values?alt=json</code>

<code>$settings['gcontent_pages'] = 'https://spreadsheets.google.com/feeds/list/1OtrzpJJk4afQZ1yxtHo1Opth48WWASncCZv45jGpmdA/1/public/values?alt=json';</code>

<code>$settings['gcontent_redirects'] = 'https://spreadsheets.google.com/feeds/list/1OtrzpJJk4afQZ1yxtHo1Opth48WWASncCZv45jGpmdA/2/public/values?alt=json';</code>

to <code>settings.php</code> or <code>settings.local.php</code>

## Usage

To import content run <code>drush mim sync_pages</code>
To see status run <code>drush ms</code>
