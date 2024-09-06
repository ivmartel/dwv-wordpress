# wp-env

> [wp-env](https://www.npmjs.com/package/@wordpress/env) lets you easily set up a local WordPress environment for building and testing plugins and themes.

No need for a global install, a local one is enough and then run from the root of the repo:

```sh
# start the environement
yarn run wp-env start

# rights on wp-content could not allow uploads
# mkdir /var/www/html/wp-content/uploads
# chmod 777 /var/www/html/wp-content/uploads

# stop the environement
yarn run wp-env stop

# clean: reset the database
yarn run wp-env clean

# destroy: destroy everything and start again
yarn run wp-env destroy
```

Some refs:
 - [get-started-with-wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/)
 - [wp-env.json](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/#wp-env-json): (on 03/2024) the doc suggests to use `"core": null` but that did not work for me, what did was to go with named versions (get them at: [wordpress release notes](https://wordpress.org/news/category/releases/), [php versions](https://www.php.net/supported-versions.php))

# wp-scripts

> [wp-scripts](https://www.npmjs.com/package/@wordpress/scripts) is a collection of reusable scripts tailored for WordPress development

-> stick to v27.9.0 as changelog suggests ([28.0.0](https://github.com/WordPress/gutenberg/blob/trunk/packages/scripts/CHANGELOG.md#2800-2024-05-31)).
