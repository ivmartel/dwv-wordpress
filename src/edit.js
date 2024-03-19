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
  InspectorControls,
  useBlockProps,
} from '@wordpress/block-editor';

// block icon
import { file } from '@wordpress/icons';

/**
 * Components
 *
 * @see https://wordpress.github.io/gutenberg
 */
import {
  Placeholder,
  PanelBody,
  Button,
  TextControl,
} from '@wordpress/components';

/**
 * Get the dcm shortcode from attributes.
 *
 * @param {Object} attributes The block attributes.
 * @return {string} The shortcode.
 */
export function getDcmShortcode( attributes ) {
  let shortcode = '[dcm';
  // ids: number[]
  const ids = attributes.ids;
  // return empty string if no ids
  if ( ids === undefined || ids.length === 0 ) {
    return '';
  }
  shortcode += ' ids="' + ids.toString() + '"';
  // number attributes
  const attributeNames = [ 'height', 'width', 'window_center', 'window_width' ];
  for ( const attributeName of attributeNames ) {
    const att = attributes[ attributeName ];
    if ( att !== undefined && att !== 0 ) {
      shortcode += ' ' + attributeName + '="' + att + '"';
    }
  }
  shortcode += ']';
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

  // set an number attribute from its key and string value
  const setIntAttribute = ( key, value ) => {
    value = parseInt( value, 10 );
    // avoid NaN
    if ( isNaN( value ) ) {
      value = 0;
    }
    const obj = {};
    obj[ key ] = value;
    setAttributes( obj );
  };

  return (
    <div { ...blockProps }>
      <InspectorControls>
        <PanelBody
          title={ __( 'View Size (optional)', 'dicomsupport' ) }
          initialOpen={ false }
        >
          <TextControl
            label={ __( 'Height (px)', 'dicomsupport' ) }
            value={ attributes.height || 0 }
            onChange={ ( value ) => setIntAttribute( 'height', value ) }
          />
          <TextControl
            label={ __( 'Width (px)', 'dicomsupport' ) }
            value={ attributes.width || 0 }
            onChange={ ( value ) => setIntAttribute( 'width', value ) }
          />
        </PanelBody>
        <PanelBody
          title={ __( 'Window level (optional)', 'dicomsupport' ) }
          initialOpen={ false }
        >
          <TextControl
            label={ __( 'Window center', 'dicomsupport' ) }
            value={ attributes.window_center || 0 }
            onChange={ ( value ) => setIntAttribute( 'window_center', value ) }
          />
          <TextControl
            label={ __( 'Window width', 'dicomsupport' ) }
            value={ attributes.window_width || 0 }
            onChange={ ( value ) => setIntAttribute( 'window_width', value ) }
          />
        </PanelBody>
      </InspectorControls>
      { attributes && ! isSelected ? (
        <div>{ getDcmShortcode( attributes ) }</div>
      ) : (
        <Placeholder
          icon={ file }
          label={ __( 'DICOM Images', 'dicomsupport' ) }
          instructions={ __(
            'Select or modify listed files from the media library.',
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
