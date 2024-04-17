Publish the plugin to wordpress
===============================

On the githup repo:
1. Update the `readme.txt` changelog and the `DicomSupport.php` `Version`
1. Commit: `git commit -a -m "Prepare release v#.#.#"`
1. Push: `git push origin main`
1. Run the `build-plugin.sh` to create the 'build/DicomSupport' folder

On the svn repo:
1. Copy the 'build/DicomSupport' folder to the svn 'trunk' folder
1. Run a the following to check for possible delete or adds:
    * add: `svn status | grep ^? | sed 's/?    //' | xargs svn add`
    * delete: `svn status | grep ^! | sed 's/!    //' | xargs svn rm`
1. Commit: `svn commit -m "Update trunk for release v#.#.#"`
1. Create svn tag: `svn copy trunk tags/#.#.#`
1. Update the tag's `readme.txt` `Stable tag` to the release version
1. Commit tag: `svn commit -m "Release v#.#.#"`
1. Update the trunk `readme.txt` `Stable tag` to the release version (will trigger the wordpress publish)
1. `svn commit -m "Publish v#.#.#"`

On the githup repo:
1. Update the `readme.txt` `stable tag`
1. Commit: `git commit -a -m "Release v#.#.#"`
1. Push: `git push origin main`
1. Create a github release to tag the code

SVN web browser: https://plugins.trac.wordpress.org/browser/dicom-support/
