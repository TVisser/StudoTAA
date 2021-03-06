<?php

    class ScheduleNavigator
    {
        /**
         * Filters out all the href anchors from a raw HTML string.
         *
         * @param   string  A raw HTML string containing href anchors.
         * @return  array   An array of the href-anchors from the $_html.
         */
        protected function split_anchors($_html)
        {
            $DOM = new DOMDocument;
            $DOM->loadHTML($_html);

            $xpath = new DOMXPath($DOM);
            $nodes = $xpath->query('//a/@href');
            $return = array();
            foreach($nodes as $href) {
                $return[] = $href->nodeValue;
            }

            return $return;
        }

        /**
         * Loads the HTML of the classes-overview page,
         * splits the page into rows and fills an array in the 'class-names => url' format.
         *
         * @param   string  The url of the classes-overview page.
         * @return  array   An array filled in the 'class-names => url' format.
         */
        protected function parse_classes($_url)
        {
            $HTML = file_get_contents($_url);

            $DOM = new DOMDocument;
            $DOM->loadHTML($HTML);

            $items = $DOM->getElementsByTagName('tr');
            $url = substr($_url, 0, -9);

            $values = array();
            foreach ($items as $node) {
                $href = $this->split_anchors($node->ownerDocument->saveHTML($node));
                if(!empty($href[count($href) - 1]) && $href[count($href) - 1] != '../../index.htm') {
                    $values[$node->nodeValue] = $url . $href[count($href) - 1];
                }
            }

            return $values;
        }

        /**
         * Navigate through the sector- and classes selection to get the desired URLs for the ScheduleLoader class.
         *
         * @param   string      The name of the sector you want to search.
         * @return  array       Return the list of anchors formatted like 'class-names => url'.
         */
        public function get_schedule_urls($_sector)
        {
            $overview_HTML = $this->split_anchors(file_get_contents('http://roosters.roc-teraa.nl/rooster_uitwisseling/' . $_sector));
            $anchors = $this->parse_classes('http://roosters.roc-teraa.nl/rooster_uitwisseling/' . $_sector . '/' . $overview_HTML[count($overview_HTML) - 1]);

            return $anchors;
        }
    }