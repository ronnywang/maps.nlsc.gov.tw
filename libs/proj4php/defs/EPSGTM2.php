<?php
Proj4php::$defs["EPSG:TM2"] = "+proj=tmerc +ellps=GRS80 +lon_0=121 +x_0=250000 +k=0.9999";
//Proj4php::$defs["EPSG:TM2"] = "+proj=tmerc +ellps=aust_SA +towgs84=-764.558,-361.229,-178.374,-.0000011698,.0000018398,.0000009822,.00002329 +lon_0=121 +x_0=250000 +k=0.9999 +to +proj=tmerc +datum=WGS84 +lon_0=121 +x_0=250000 +k=0.9999";
Proj4php::$defs['EPSG:TM2'] = '+proj=tmerc +lat_0=0 +lon_0=121 +k=0.9999 +x_0=250000 +y_0=0 +ellps=GRS80 +units=m +no_defs ';
