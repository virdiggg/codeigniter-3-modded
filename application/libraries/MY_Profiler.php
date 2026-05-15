<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Profiler extends CI_Profiler {

    /**
    * Override the view method to add custom behavior
    *
    * @return string
    */
    public function run()
    {
        $output = '<style>
            .custom-btn-benchmark {
                position: fixed;
                // display: none;
                left: 30px;
                bottom: 30px;
                z-index: 9999;
                // animation: action 1s infinite alternate;
                background-color: #007bff !important;
                color: #fff;
                border: none;
                padding: 10px 20px !important;
                border-radius: 5px !important;
                // cursor: pointer;
                font-size: 16px !important;
                width: auto; /* or specify a fixed width like width: 50%; */
                display: inline-block; /* This prevents the element from taking up 100% width */
            }
            .custom-btn-benchmark:hover {
                background-color: #0056b3;
            }
            .custom-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 9998;
            }
            .custom-modal.show {
                display: flex;
            }
            .custom-modal-content {
                background-color: #fff;
                padding: 20px;
                border-radius: 5px;
                max-width: 90%;
                max-height: 90%;
                overflow-y: auto;
            }
        </style>
        <button type="button" class="custom-btn-benchmark" id="btnBenchmark">Benchmark</button>
        <div class="custom-modal" id="benchmarkModal">
            <div class="custom-modal-content">';

        $fields_displayed = 0;

        foreach ($this->_available_sections as $section)
        {
            if ($this->{'_compile_'.$section} !== FALSE)
            {
                $func = '_compile_'.$section;
                $output .= $this->{$func}();
                $fields_displayed++;
            }
        }

        if ($fields_displayed === 0)
        {
            $output .= '<p style="border:1px solid #5a0099;padding:10px;margin:20px 0;background-color:#eee;">'
                . $this->CI->lang->line('profiler_no_profiles') . '</p>';
        }

        $output .= '</div></div>
        <script>
            document.getElementById("btnBenchmark").addEventListener("click", function() {
                var modal = document.getElementById("benchmarkModal");
                modal.classList.toggle("show");
            });
            document.getElementById("benchmarkModal").addEventListener("click", function(e) {
                if (e.target === this) {
                    this.classList.remove("show");
                }
            });
        </script>';

        return $output;
    }
}