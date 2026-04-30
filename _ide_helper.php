<?php

/**
 * IDE Helper file for CodeIgniter 4
 * This file is not used by the application, it's only for the IDE to recognize global functions and classes.
 */

namespace {
    /**
     * @return string
     */
    function base_url(string $path = '') {}

    /**
     * @param mixed $data
     * @return string
     */
    function esc($data, string $context = 'html', ?string $encoding = null) {}

    /**
     * @param string $path
     * @return string
     */
    function site_url(string $path = '') {}

    /**
     * @return \CodeIgniter\Session\Session
     */
    function session() {}

    /**
     * @return string
     */
    function csrf_field() {}

    /**
     * @return string
     */
    function csrf_token() {}

    /**
     * @return string
     */
    function csrf_hash() {}

    /**
     * @param string $key
     * @return mixed
     */
    function old(string $key) {}
}

namespace CodeIgniter\View {
    class View {
        public function extend(string $layout) {}
        public function section(string $name) {}
        public function endSection() {}
        public function renderSection(string $name) {}
    }
}
