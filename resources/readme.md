Publish the plugin to wordpress
===============================

1. Run the `build-plugin.sh` to create the 'build/DicomSupport' folder
1. Copy to svn
1. Run a `svn status` to check for possible delete or adds
1. Commit: `svn commit -m "Update trunk for release v0.#.0"`
1. Create svn tag: `svn copy trunk tags/0.#.0 -m "Release v0.#.0"`
1. Update the `readme.txt` `Stable tag` to the release version (will trigger the wordpress publish)
1. `svn commit -m "Publish v0.#.0"`

SVN web browser: https://plugins.trac.wordpress.org/browser/dicom-support/
