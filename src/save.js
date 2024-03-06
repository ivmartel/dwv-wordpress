/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

// 'dcm' shortcode creation function
import { getDcmShortcode } from './edit';

/**
 * The save arguements.
 *
 * @typedef {Object} SaveArgs
 * @property {Object} attributes The block attributes.
 */

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @param {SaveArgs} args The save arguments.
 * @return {HTMLElement} Element to render.
 */
export default function save( { attributes } ) {
  const blockProps = useBlockProps.save();
  return <div { ...blockProps }>{ getDcmShortcode( attributes.ids ) }</div>;
}
