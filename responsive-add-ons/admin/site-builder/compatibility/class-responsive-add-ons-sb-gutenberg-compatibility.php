<?php
/**
 * Site Builder Gutenberg Compatibility class
 *
 * @package Responsive_Add_Ons
 * @since 3.3.0
 */

if( ! class_exists( 'Responsive_Add_Ons_SB_Gutenberg_Compatibility' ) ) {

    /**
     * Site Builder Gutenberg Builder Compatibility class
     *
     * @since 3.3.0
     */
    class Responsive_Add_Ons_SB_Gutenberg_Compatibility extends Responsive_Add_Ons_Site_Builder_compatibility {
        /**
         * Render Blocks content for post.
         *
         * @param int $post_id Post id.
         *
         * @since 3.3.0
         */
        public function render_content( $post_id ) {
            $output       = '';
            $current_post = get_post( $post_id, OBJECT );
    
            if ( has_blocks( $current_post ) ) {
                $blocks = parse_blocks( $current_post->post_content );
                foreach ( $blocks as $block ) {
                    $output .= render_block( $block );
                }
    
                // Automatically embed URLs (like Vimeo) using WP_Embed.
                if ( class_exists( 'WP_Embed' ) ) {
                    $wp_embed = new WP_Embed();
                    $output   = $wp_embed->autoembed( $output );
                }
            } else {
                $output = $current_post->post_content;
            }
    
            // Process nested shortcodes as it's for site builder and remove automatic <p> tags around them.
            echo do_shortcode( do_shortcode( shortcode_unautop( $output ) ) );
        }

        /**
         * Load Gutenberg Blocks styles & scripts.
         *
         * @param int $post_id Post id.
         *
         * @since 3.3.0
         */
        public function enqueue_blocks_assets( $post_id ) {

            if( defined( 'RESPONSIVE_BLOCK_EDITOR_ADDONS_VER' ) ) {
                $post_css                = '';
                $current_post            = get_post( $post_id, OBJECT );
                $active_gutenberg_blocks = parse_blocks( $current_post->post_content );

                if( class_exists( 'Responsive_Block_Editor_Addons_Frontend_Styles_Helper' ) ) {
                    $rba_frontend_styles_helper = Responsive_Block_Editor_Addons_Frontend_Styles_Helper::get_instance();
                    $post_css .= $rba_frontend_styles_helper->get_styles( $active_gutenberg_blocks );
                }

                if ( ! empty( $post_css ) ) {
                    echo "<style id='responsive-addons-rba_blocks-frontend-styles'>$post_css</style>"; //phpcs:ignore
                }
            }
        }        
    }

}