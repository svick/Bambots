<?php
/**
 Copyright 2013 Myers Enterprises II

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */

namespace com_brucemyers\InceptionBot;

class RuleSet
{
    const COMMENT_REGEX = '/<!--[0x00-0xff]*?-->/';
    const WIKI_TEMPLATE_REGEX = '/{{[0x00-0xff]+?}}/';
    const RULE_REGEX = '!^(-?\\d*)\\s*(/.*?/)((?:\\s*,\\s*/.*?/)*)$!';
    const SCORE_REGEX = '/^@@\\s*(\\d+)\\s*@@$/';
    const TEMPLATE_LINE_REGEX = '/^(-?\\d*)\\s*\\$\\$(.*)\\$\\$$/';
    const TEMPLATE_REGEX = '!^/\\s*\\$\\$(.*)\\$\\$\\s*/$!';
    const SIZE_REGEX = '!^/\\s*\\$SIZE\\s*(<|>)\\s*(\\d+)\\s*/$!';
    const INIHIBITOR_REGEX = '!\\s*,\\s*(/.*?/)!';
    const DEFAULT_SCORE = 10;

    public $errors = array();
    public $rules = array();
    public $minScore = self::DEFAULT_SCORE;

    /**
     * Constructor
     *
     * @param $data string Rule data
     */
    public function __construct($data)
    {
        // Strip comments/templates
        $data = preg_replace(self::COMMENT_REGEX, '', $data);
        $data = preg_replace(self::WIKI_TEMPLATE_REGEX, '', $data);
        $lines = preg_split('/\\r?\\n/', $data);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match(self::RULE_REGEX, $line, $matches)) {
                $rule = $this->parseRule($line, $matches);
                if ($rule['valid']) $this->rules[] = $rule;
            } elseif (preg_match(self::SCORE_REGEX, $line, $matches)) {
                $this->minScore = $matches[1];
            } elseif (preg_match(self::TEMPLATE_LINE_REGEX, $line, $matches)) {
                $score = $matches[1];
                if (empty($score)) $score = self::DEFAULT_SCORE;
                $regex = '/\\{\\{' . $matches[2] . '.*?\\}\\}/';
                $this->rules[] = array('score' => $score, 'regex' => $regex, 'valid' => true, 'size' => false, 'inhibitors' => array());
            } else {
                $this->errors[] = 'Invalid rule: ' . $line;
            }
        }

        if (empty($this->rules)) $this->errors[] = 'No rules found';
    }

    /**
     * Parse a rule line
     *
     * @param $line string Rule line
     * @param $matches Match data
     * @return array Rule data
     */
    protected function parseRule(&$line, &$matches)
    {
        $score = $matches[1];
        if (empty($score)) $score = self::DEFAULT_SCORE;
        $regex = $matches[2];
        if (preg_match(self::TEMPLATE_REGEX, $regex, $tmplmatches)) {
            $regex = '/\\{\\{' . $tmplmatches[1] . '.*?\\}\\}/';
        }

        $size = preg_match(self::SIZE_REGEX, $regex, $sizematches);
        $valid = true;

        if (! $size) {
            $valid = (preg_match($regex, '') !== false);
            echo ($valid ? 'true' : 'false') . "\n";
            if (! $valid) $this->errors[] = 'Invalid pattern in rule: ' . $line;
        }

        $rule = array('score' => $score, 'regex' => $regex, 'valid' => $valid, 'size' => false, 'inhibitors' => array());

        if ($size) {
            $rule['size'] = true;
            $rule['sizeoperator'] = $sizematches[1];
            $rule['sizeoperand'] = $sizematches[2];
        }

        // Process the inhibitors
        if (count($matches) > 3) {
            preg_match_all(self::INIHIBITOR_REGEX, $matches[3], $inhibmatches, PREG_PATTERN_ORDER);
            foreach ($inhibmatches[1] as $regex) {
                if (preg_match(self::TEMPLATE_REGEX, $regex, $tmplmatches)) {
                    $regex = '/\\{\\{' . $tmplmatches[1] . '.*?\\}\\}/';
                }

            	$valid = (preg_match($regex, '') !== false);
            	if (! $valid) {
            	    $this->errors[] = "Invalid inhibitor ($regex) in rule: $line";
            	    $rule['valid'] = false;
            	}
            	$rule['inhibitors'][] = $regex;
            }
        }

        return $rule;
    }
}
