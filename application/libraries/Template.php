<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Template
 *
 * A small Blade-like layout engine for CodeIgniter 3, built on output
 * buffering. A child view declares its layout with extend(), fills named
 * slots with section()/end(), and the layout prints them with yield_section().
 *
 * @property CI_Loader $load
 * @property CI_Output $output
 */
class Template
{
    /**
     * CodeIgniter super object.
     *
     * @var CI_Controller
     */
    protected $CI;

    /** @var string|null */
    protected $layout = null;

    /** @var array<string, string> */
    protected $sections = [];

    /** @var array<string, string[]> */
    protected $pushes = [];

    /** @var array<int, array{0: string, 1: string}> */
    protected $stack = [];

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * Render a child view inside its declared layout(s).
     *
     * @param string               $view   View path, e.g. 'admin/dashboard'
     * @param array<string, mixed> $data   Variables passed to every view
     * @param bool                 $return TRUE to return the HTML instead of sending it to the output
     * @return string|void         The final HTML when $return is TRUE
     */
    public function render(string $view, array $data = [], bool $return = false)
    {
        $this->layout   = null;
        $this->sections = [];
        $this->pushes   = [];
        $this->stack    = [];

        // Render the child view first. It may call extend() and define sections.
        $html = $this->CI->load->view($view, $data, true);

        // Render the layout chain (a layout may itself extend another layout).
        while ($this->layout !== null) {
            $layout       = $this->layout;
            $this->layout = null;
            $html         = $this->CI->load->view($layout, $data, true);
        }

        if ($return) {
            return $html;
        }

        $this->CI->output->append_output($html);
    }

    /**
     * Declare which layout the current view extends.
     *
     * @param string $layout Layout view path, e.g. 'layout/home'
     */
    public function extend(string $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Define a section. Pass $content for a one-line section, or omit it to
     * open a buffer that must be closed with end().
     *
     * @param string      $name    Section name
     * @param string|null $content Inline content, or null to start buffering
     */
    public function section(string $name, $content = null)
    {
        if ($content !== null) {
            $this->sections[$name] = $content;
            return;
        }

        $this->stack[] = [$name, 'section'];
        ob_start();
    }

    /**
     * Start appending to a named stack (like Blade's @push). Several views
     * can push to the same name, for example 'scripts'. Close with end().
     *
     * @param string $name Stack name
     */
    public function push(string $name)
    {
        $this->stack[] = [$name, 'push'];
        ob_start();
    }

    /**
     * Close the innermost open section() or push() buffer.
     *
     * @throws LogicException When there is no open section or push
     */
    public function end()
    {
        if (empty($this->stack)) {
            throw new LogicException('Template::end() called without an open section() or push().');
        }

        list($name, $type) = array_pop($this->stack);
        $buffer = (string) ob_get_clean();

        if ($type === 'push') {
            $this->pushes[$name][] = $buffer;
        } else {
            $this->sections[$name] = $buffer;
        }
    }

    /**
     * Output a section's content (like Blade's @yield).
     *
     * @param string $name    Section name
     * @param string $default Returned when the section was never defined
     */
    public function yield_section(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Output everything pushed to a named stack (like Blade's @stack).
     *
     * @param string $name Stack name
     */
    public function stack(string $name): string
    {
        return isset($this->pushes[$name]) ? implode('', $this->pushes[$name]) : '';
    }
}