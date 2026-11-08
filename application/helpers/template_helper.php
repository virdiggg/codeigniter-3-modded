<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Template helper
 *
 * Thin wrappers around the Template library so views can call them directly.
 * Views run inside CI_Loader, so $this is not the controller, which is why
 * these are plain functions.
 */

if ( ! function_exists('tpl'))
{
    /**
     * Get the Template library instance, loading it on first use.
     */
    function tpl(): Template
    {
        $CI =& get_instance();
        isset($CI->template) OR $CI->load->library('template');

        return $CI->template;
    }
}

if ( ! function_exists('tpl_extend'))
{
    /**
     * Declare the layout this view extends.
     *
     * @param string $layout Layout view path, e.g. 'layout/home'
     */
    function tpl_extend(string $layout)
    {
        tpl()->extend($layout);
    }
}

if ( ! function_exists('tpl_section'))
{
    /**
     * Define a section. Give $text for a one-liner, or omit it to open a
     * buffer closed by tpl_end().
     *
     * @param string      $name Section name
     * @param string|null $text Inline content, or null to start buffering
     */
    function tpl_section(string $name, $text = null)
    {
        tpl()->section($name, $text);
    }
}

if ( ! function_exists('tpl_push'))
{
    /**
     * Start appending to a named stack, e.g. 'scripts'. Close with tpl_end().
     *
     * @param string $name Stack name
     */
    function tpl_push(string $name)
    {
        tpl()->push($name);
    }
}

if ( ! function_exists('tpl_end'))
{
    /**
     * Close the innermost open tpl_section() or tpl_push().
     */
    function tpl_end()
    {
        tpl()->end();
    }
}

if ( ! function_exists('tpl_yield'))
{
    /**
     * Get a section's content for printing in a layout.
     * Named tpl_yield because "yield" is a reserved word in PHP.
     *
     * @param string $name    Section name
     * @param string $default Fallback when the section is not defined
     */
    function tpl_yield(string $name, string $default = ''): string
    {
        return tpl()->yield_section($name, $default);
    }
}

if ( ! function_exists('tpl_stack'))
{
    /**
     * Get everything pushed to a named stack for printing in a layout.
     *
     * @param string $name Stack name
     */
    function tpl_stack(string $name): string
    {
        return tpl()->stack($name);
    }
}

if ( ! function_exists('assets'))
{
    /**
     * Get file from ASSETS_PATH
     *
     * @param string $name Full path of asset file
     */
    function assets(string $name): string
    {
        return ASSETS_PATH . trim('//', $name);
    }
}

if ( ! function_exists('uploads'))
{
    /**
     * Get file from UPLOAD_PATH
     *
     * @param string $name Full path of uploaded file
     */
    function uploads(string $name): string
    {
        return UPLOAD_PATH . trim('//', $name);
    }
}
