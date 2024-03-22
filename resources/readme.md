Publish the plugin to wordpress
===============================

1. Run the `build-plugin.sh` to create the 'build/DicomSupport' folder
1. Copy it to the svn 'trunk' folder
1. Run a `svn status` to check for possible delete or adds
 * add: `svn status | grep ^? | sed 's/?    //' | xargs svn add`
 * delete: `svn status | grep ^! | sed 's/!    //' | xargs svn rm`
1. Commit: `svn commit -m "Update trunk for release v0.#.0"`
1. Create svn tag: `svn copy trunk tags/0.#.0`
1. Update the tag's `readme.txt` `Stable tag` to the release version
1. Commit tag: `svn commit -m "Release v0.#.0"`
1. Update the trunk `readme.txt` `Stable tag` to the release version (will trigger the wordpress publish)
1. `svn commit -m "Publish v0.#.0"`

SVN web browser: https://plugins.trac.wordpress.org/browser/dicom-support/
