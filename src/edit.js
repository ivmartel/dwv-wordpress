/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 * @see https://github.com/WordPress/gutenberg/blob/HEAD/packages/block-editor/src/components/media-upload/README.md
 */
import {
  MediaUpload,
  MediaUploadCheck,
  useBlockProps,
} from '@wordpress/block-editor';

// block icon
import { file } from '@wordpress/icons';

/**
 * Components
 *
 * @see https://wordpress.github.io/gutenberg
 */
import { Placeholder, Button } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * Get the dcm shortcode from a list of ids.
 *
 * @param {Array} ids List of ids.
 * @return {string} The shortcode.
 */
export function getDcmShortcode( ids ) {
  let shortcode = '';
  if ( ids !== undefined && ids.length !== 0 ) {
    shortcode = '[dcm ids="' + ids.toString() + '"]';
  }
  return shortcode;
}

/**
 * The edit arguments.
 *
 * @typedef {Object} EditArgs
 * @property {Object}   attributes    The block attributes.
 * @property {boolean}  isSelected    Is the block selected?
 * @property {Function} setAttributes The block attributes setter.
 */

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {EditArgs} args The edit arguments.
 * @return {HTMLElement} Element to render.
 */
export default function Edit( { attributes, isSelected, setAttributes } ) {
  const blockProps = useBlockProps();

  // set the attributes ids from a media list
  const onMediaSelect = ( media ) => {
    const ids = [];
    media.map( ( item ) => {
      ids.push( item.id );
      return item;
    } );
    setAttributes( { ids } );
  };

  return (
    <div { ...blockProps }>
      { attributes.ids && ! isSelected ? (
        <div>{ getDcmShortcode( attributes.ids ) }</div>
      ) : (
        <Placeholder
          icon={ file }
          label={ __( 'DICOM Images', 'dicomsupport' ) }
          instructions={ __(
            'Select or modify files from the media library.',
            'dicomsupport'
          ) }
        >
          <MediaUploadCheck>
            <MediaUpload
              onSelect={ onMediaSelect }
              allowedTypes={ [ 'application/dicom' ] }
              value={ attributes.ids }
              multiple={ true }
              render={ ( { open } ) => (
                <Button variant="primary" onClick={ open }>
                  Media Library
                </Button>
              ) }
            />
          </MediaUploadCheck>
        </Placeholder>
      ) }
    </div>
  );
}
