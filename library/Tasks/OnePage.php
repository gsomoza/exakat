<?php
/*
 * Copyright 2012-2015 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/


namespace Tasks;

class OnePage extends Tasks {
    private $project_dir = '.';
    private $config = null;
    
    protected $themes = array('CompatibilityPHP53', 'CompatibilityPHP54', 'CompatibilityPHP55', 'CompatibilityPHP56', 'CompatibilityPHP70',
                              'OneFile');
    const TOTAL_STEPS = 11; //

    protected $reports = array('Onepage' => array('Json'   => 'report'));
    
    public function run(\Config $config) {
        $this->config = $config;
        
        $progress = 0;
        
        $begin = microtime(true);
        $project = 'onepage';
        $this->project_dir = $config->projects_root.'/projects/'.$project;

        // checking for installation
        if (!file_exists($this->project_dir)) {
            shell_exec('php '.$config->executable.' init -p onepage ');
            mkdir($this->project_dir.'/code', 0755);
            shell_exec('php '.$config->executable.' phploc -p onepage ');
        }

        // todo : check that there is indeed this project or create it.
        if (!file_exists($config->filename)) {
            die("Can't find the file '$config->filename'. Aborting\n");
        }

        copy($config->filename, $config->projects_root.'/projects/'.$project.'/code/onepage.php');
        $this->reports['Onepage']['Json'] = 'onepage';
        
        $this->cleanLog($config->projects_root.'/projects/'.$project.'/log/');
        $this->logTime('Start');

        $datastorePath = $config->projects_root.'/projects/'.$project.'/datastore.sqlite';
        if (file_exists($datastorePath)) {
            unlink($datastorePath);
        }
        
        // cleaning datastore
        $this->cleanLog($config->projects_root.'/projects/'.$project.'/log/');

        unset($this->datastore);
        $this->datastore = new \Datastore($config);
        
        $this->datastore->cleanTable('hash');
        $audit_start = time();
        $this->datastore->addRow('hash', array('audit_start' => $audit_start,
                                               'exakat_version' => \Exakat::VERSION,
                                               'exakat_build' => \Exakat::BUILD,
                                               ));

        $thread = new \Thread();
        display("Running project '$project'\n");

        shell_exec('php '.$config->executable.' load -v -r -d '.$config->projects_root.'/projects/'.$project.'/code/ -p '.$project. ' > '.$config->projects_root.'/projects/'.$project.'/log/load.final.log' );
        display("Project loaded\n");
        $this->updateProgress($progress++);
        $this->logTime('Loading');

        $res = shell_exec('php '.$config->executable.' build_root -v -p '.$project.' > '.$config->projects_root.'/projects/'.$project.'/log/build_root.final.log');
        display("Build root\n");
        $this->updateProgress($progress++);
        $this->logTime('Build_root');

        $res = shell_exec('php '.$config->executable.' tokenizer -p '.$project.' > '.$config->projects_root.'/projects/'.$project.'/log/tokenizer.final.log');
        if (!empty($res) && strpos('javax.script.ScriptException', $res) !== false) {
            file_put_contents($config->projects_root.'/log/tokenizer_error.log', $res);
            die();
        }

        $this->logTime('Tokenizer');
        display("Project tokenized\n");
        $this->updateProgress($progress++);

        $processes = array();
        foreach($this->themes as $theme) {
            $themeForFile = strtolower(str_replace(' ', '_', trim($theme, '"')));
            shell_exec('php '.$config->executable.' analyze -norefresh -p '.$project.' -T '.$theme.' > '.$config->projects_root.'/projects/'.$project.'/log/analyze.'.$themeForFile.'.final.log;
mv '.$config->projects_root.'/projects/'.$project.'/log/analyze.log '.$config->projects_root.'/projects/'.$project.'/log/analyze.'.$themeForFile.'.log');
            display("Analyzing $theme\n");
            $this->updateProgress($progress++);
        }

        display("Project analyzed\n");
        $this->updateProgress($progress++);
        $this->logTime('Analyze');

        shell_exec('php '.$config->executable.' onepagereport -p onepage');
        $this->logTime('Report');

        display("Project reported\n");
        $this->updateProgress($progress++);

        unlink($config->projects_root.'/projects/'.$project.'/code/onepage.php');

        $audit_end = time();
        $this->datastore->addRow('hash', array('audit_end'    => $audit_end,
                                               'audit_length' => $audit_end - $audit_start));

        $this->logTime('Final');
        $this->updateProgress($progress++);
        display("End 2\n");
        $end = microtime(true);
        display("Total time : ".number_format(($end - $begin), 2)."s\n");
        
        $this->logTime('Files');
    }

    private function updateProgress($status) {
        $progress = json_decode(file_get_contents($this->config->projects_root.'/progress/jobqueue.exakat'));
        $progress->progress = number_format(100 * $status / self::TOTAL_STEPS, 0);
        file_put_contents($this->config->projects_root.'/progress/jobqueue.exakat', json_encode($progress));
    }

    private function cleanLog($path) {
        // cleaning log directory (possibly logs)
        $logs = glob("$path/*");
        foreach($logs as $log) {
            unlink($log);
        }
    }

    private function logTime($step) {
        static $log, $begin, $end, $start;

        if ($log === null) {
            $log = fopen($this->project_dir.'/log/project.timing.csv', 'w+');
        }
        $end = microtime(true);
        if ($begin === null) {
            $begin = $end;
            $start = $end;
        }

        fwrite($log, $step."\t".($end - $begin)."\t".($end - $start)."\n");
        $begin = $end;
    }
}

?>
