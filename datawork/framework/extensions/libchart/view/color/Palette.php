<?php
    /* Libchart - PHP chart library
     * Copyright (C) 2005-2011 Jean-Marc Tr�meaux (jm.tremeaux at gmail.com)
     * 
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     * 
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/>.
     * 
     */
    
    /**
     * Color palette shared by all chart types.
     *
     * @author Jean-Marc Tr�meaux (jm.tremeaux at gmail.com)
     * Created on 25 july 2007
     */
    class Palette {
        // Plot attributes
        public $red;
        public $axisColor;
        public $backgroundColor;
        
        // Specific chart attributes
        public $barColorSet;
        public $lineColorSet;
        public $pieColorSet;
    
        /**
         * Palette constructor.
         */
        public function Palette() {
            $this->red = new Color(255, 0, 0);
        
            // Set the colors for the horizontal and vertical axis
            $this->setAxisColor(array(
                new Color(255, 255, 255),
                new Color(255, 255, 255)
            ));
            // Set the colors for the background
            $this->setBackgroundColor(array(
                new Color(255, 255, 255),
                new Color(255, 255, 255),
                new Color(255, 255, 255),
                new Color(255, 255, 255)
            ));
            
            // Set the colors for the bars
            $this->setBarColor(array(
                new Color(124,181,236),
                new Color(67,67,72),
                new Color(144,237,125),
                new Color(247,163,92),
                new Color(128,133,233),
                new Color(241,92,128),
                new Color(288,211,84),
                new Color(141,70,83),
                new Color(30,144,255),
                new Color(108,123,139),
                new Color(255,0,255),
                new Color(0,0,255)
            ));

            // Set the colors for the lines
            $this->setLineColor(array(
                new Color(124,181,236),
                new Color(67,67,72),
                new Color(144,237,125),
                new Color(247,163,92),
                new Color(128,133,233),
                new Color(241,92,128),
                new Color(288,211,84),
                new Color(141,70,83),
                new Color(30,144,255),
                new Color(108,123,139),
                new Color(255,0,255),
                new Color(0,0,255)
            ));

            // Set the colors for the pie
            $this->setPieColor(array(
                new Color(124,181,236),
                new Color(67,67,72),
                new Color(144,237,125),
                new Color(247,163,92),
                new Color(128,133,233),
                new Color(241,92,128),
                new Color(288,211,84),
                new Color(141,70,83),
                new Color(30,144,255),
                new Color(108,123,139),
                new Color(255,0,255),
                new Color(0,0,255)
            ));
        }
        
        /**
         * Set the colors for the axis.
         *
         * @param colors Array of Color
         */
        public function setAxisColor($colors) {
            $this->axisColor = $colors;
        }
        
        /**
         * Set the colors for the background.
         *
         * @param colors Array of Color
         */
        public function setBackgroundColor($colors) {
            $this->backgroundColor = $colors;
        }
        
        /**
         * Set the colors for the bar charts.
         *
         * @param colors Array of Color
         */
        public function setBarColor($colors) {
            $this->barColorSet = new ColorSet($colors, 0.75);
        }
        
        /**
         * Set the colors for the line charts.
         *
         * @param colors Array of Color
         */
        public function setLineColor($colors) {
            $this->lineColorSet = new ColorSet($colors, 0.75);
        }
        
        /**
         * Set the colors for the pie charts.
         *
         * @param colors Array of Color
         */
        public function setPieColor($colors) {
            $this->pieColorSet = new ColorSet($colors, 0.7);
        }
    }
?>