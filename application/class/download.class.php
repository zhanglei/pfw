<?php
/** 
 * Class Download 
 * 
 * This class can used to handle file downloads. 
 * You can define a file to start a download process with the method getFile().
 * If the propertie 'statistic' is true, for each downloaded file will be
 * written the download count in a defined counter file.
 * The method getCounter() returns an array of all downloaded files and their hits.
 * If you pass a defined file to the method , only the count of each file will be returned.
 * You also can use the methods setVar/getVar to get/set the class properties.
 *
 * @author Marko Schulz <info@tuxnet24.de>
 * @copyright Copyright (c) 2012 tuxnet24.de
 * @license http://www.php.net/license/3_01.txt  PHP License 3.01
 * @date $Date: 2012-05-15 14:21:16 +0100 (Di, 15 Mai 2012) $
 * @version $HeadURL: http://svn.tuxnet24.de/php/classes/download.class.php $ - $Revision: 7 $
 * @package
 */

class Download {


    /**
     * class propertie: couterfile
     *
     * @var string
     * @access protected
     * @uses $this->couterfile
     */
	protected $couterfile = "./download-counter.txt";


    /**
     * class propertie: statistic
     *
     * @var bool
     * @access protected
     * @uses $this->statistic
     */
	protected $statistic = false;


    /**
     * class propertie: EXTENSION
     *
     * @var array
     * @access private
     * @uses self::$EXTENSION
     */
	private static $EXTENSION = array(
			'gz' => 'application/x-gzip',
			'gzip' => 'application/x-gzip',
			'Z' => 'application/x-compress',
			'tgz' => 'application/x-gtar',
			'tar.gz' => 'application/x-gtar',
			'ez' => 'application/andrew-inset',
			'anx' => 'application/annodex',
			'atom' => 'application/atom+xml',
			'atomcat' => 'application/atomcat+xml',
			'atomsrv' => 'application/atomserv+xml',
			'lin' => 'application/bbolin',
			'cu' => 'application/cu-seeme',
			'davmount' => 'application/davmount+xml',
			'tsp' => 'application/dsptype',
			'es' => 'application/ecmascript',
			'spl' => 'application/futuresplash',
			'hta' => 'application/hta',
			'jar' => 'application/java-archive',
			'ser' => 'application/java-serialized-object',
			'class' => 'application/java-vm',
			'js' => 'application/javascript',
			'm3g' => 'application/m3g',
			'hqx' => 'application/mac-binhex40',
			'mdb' => 'application/msaccess',
			'mxf' => 'application/mxf',
			'bin' => 'application/octet-stream',
			'oda' => 'application/oda',
			'ogx' => 'application/ogg',
			'pdf' => 'application/pdf',
			'key' => 'application/pgp-keys',
			'pgp' => 'application/pgp-signature',
			'prf' => 'application/pics-rules',
			'rar' => 'application/rar',
			'rdf' => 'application/rdf+xml',
			'rss' => 'application/rss+xml',
			'rtf' => 'application/rtf',
			'xspf' => 'application/xspf+xml',
			'zip' => 'application/zip',
			'apk' => 'application/vnd.android.package-archive',
			'cdy' => 'application/vnd.cinderella',
			'kml' => 'application/vnd.google-earth.kml+xml',
			'kmz' => 'application/vnd.google-earth.kmz',
			'xul' => 'application/vnd.mozilla.xul+xml',
			'cat' => 'application/vnd.ms-pki.seccat',
			'stl' => 'application/vnd.ms-pki.stl',
			'odc' => 'application/vnd.oasis.opendocument.chart',
			'odb' => 'application/vnd.oasis.opendocument.database',
			'odf' => 'application/vnd.oasis.opendocument.formula',
			'odg' => 'application/vnd.oasis.opendocument.graphics',
			'otg' => 'application/vnd.oasis.opendocument.graphics-template',
			'odi' => 'application/vnd.oasis.opendocument.image',
			'odp' => 'application/vnd.oasis.opendocument.presentation',
			'otp' => 'application/vnd.oasis.opendocument.presentation-template',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
			'odt' => 'application/vnd.oasis.opendocument.text',
			'odm' => 'application/vnd.oasis.opendocument.text-master',
			'ott' => 'application/vnd.oasis.opendocument.text-template',
			'oth' => 'application/vnd.oasis.opendocument.text-web',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'cod' => 'application/vnd.rim.cod',
			'mmf' => 'application/vnd.smaf',
			'sdc' => 'application/vnd.stardivision.calc',
			'sds' => 'application/vnd.stardivision.chart',
			'sda' => 'application/vnd.stardivision.draw',
			'sdd' => 'application/vnd.stardivision.impress',
			'sdf' => 'application/vnd.stardivision.math',
			'sdw' => 'application/vnd.stardivision.writer',
			'sgl' => 'application/vnd.stardivision.writer-global',
			'sxc' => 'application/vnd.sun.xml.calc',
			'stc' => 'application/vnd.sun.xml.calc.template',
			'sxd' => 'application/vnd.sun.xml.draw',
			'std' => 'application/vnd.sun.xml.draw.template',
			'sxi' => 'application/vnd.sun.xml.impress',
			'sti' => 'application/vnd.sun.xml.impress.template',
			'sxm' => 'application/vnd.sun.xml.math',
			'sxw' => 'application/vnd.sun.xml.writer',
			'sxg' => 'application/vnd.sun.xml.writer.global',
			'stw' => 'application/vnd.sun.xml.writer.template',
			'sis' => 'application/vnd.symbian.install',
			'vsd' => 'application/vnd.visio',
			'wbxml' => 'application/vnd.wap.wbxml',
			'wmlc' => 'application/vnd.wap.wmlc',
			'wmlsc' => 'application/vnd.wap.wmlscriptc',
			'wpd' => 'application/vnd.wordperfect',
			'wp5' => 'application/vnd.wordperfect5.1',
			'wk' => 'application/x-123',
			'7z' => 'application/x-7z-compressed',
			'abw' => 'application/x-abiword',
			'dmg' => 'application/x-apple-diskimage',
			'bcpio' => 'application/x-bcpio',
			'torrent' => 'application/x-bittorrent',
			'cab' => 'application/x-cab',
			'cbr' => 'application/x-cbr',
			'cbz' => 'application/x-cbz',
			'vcd' => 'application/x-cdlink',
			'pgn' => 'application/x-chess-pgn',
			'cpio' => 'application/x-cpio',
			'dms' => 'application/x-dms',
			'wad' => 'application/x-doom',
			'dvi' => 'application/x-dvi',
			'rhtml' => 'application/x-httpd-eruby',
			'mm' => 'application/x-freemind',
			'gnumeric' => 'application/x-gnumeric',
			'sgf' => 'application/x-go-sgf',
			'gcf' => 'application/x-graphing-calculator',
			'hdf' => 'application/x-hdf',
			'php' => 'application/x-httpd-php',
			'phps' => 'application/x-httpd-php-source',
			'php3' => 'application/x-httpd-php3',
			'php3p' => 'application/x-httpd-php3-preprocessed',
			'php4' => 'application/x-httpd-php4',
			'php5' => 'application/x-httpd-php5',
			'ica' => 'application/x-ica',
			'info' => 'application/x-info',
			'iii' => 'application/x-iphone',
			'iso' => 'application/x-iso9660-image',
			'jam' => 'application/x-jam',
			'jnlp' => 'application/x-java-jnlp-file',
			'jmz' => 'application/x-jmol',
			'chrt' => 'application/x-kchart',
			'kil' => 'application/x-killustrator',
			'ksp' => 'application/x-kspread',
			'latex' => 'application/x-latex',
			'lha' => 'application/x-lha',
			'lyx' => 'application/x-lyx',
			'lzh' => 'application/x-lzh',
			'lzx' => 'application/x-lzx',
			'mif' => 'application/x-mif',
			'wmd' => 'application/x-ms-wmd',
			'wmz' => 'application/x-ms-wmz',
			'msi' => 'application/x-msi',
			'nc' => 'application/x-netcdf',
			'nwc' => 'application/x-nwc',
			'o' => 'application/x-object',
			'oza' => 'application/x-oz-application',
			'p7r' => 'application/x-pkcs7-certreqresp',
			'crl' => 'application/x-pkcs7-crl',
			'qtl' => 'application/x-quicktimeplayer',
			'rpm' => 'application/x-redhat-package-manager',
			'rb' => 'application/x-ruby',
			'shar' => 'application/x-shar',
			'scr' => 'application/x-silverlight',
			'sv4cpio' => 'application/x-sv4cpio',
			'sv4crc' => 'application/x-sv4crc',
			'tar' => 'application/x-tar',
			'gf' => 'application/x-tex-gf',
			'pk' => 'application/x-tex-pk',
			'man' => 'application/x-troff-man',
			'me' => 'application/x-troff-me',
			'ms' => 'application/x-troff-ms',
			'ustar' => 'application/x-ustar',
			'src' => 'application/x-wais-source',
			'wz' => 'application/x-wingz',
			'crt' => 'application/x-x509-ca-cert',
			'xcf' => 'application/x-xcf',
			'fig' => 'application/x-xfig',
			'xpi' => 'application/x-xpinstall',
			'amr' => 'audio/amr',
			'awb' => 'audio/amr-wb',
			'axa' => 'audio/annodex',
			'flac' => 'audio/flac',
			'm3u' => 'audio/mpegurl',
			'sid' => 'audio/prs.sid',
			'gsm' => 'audio/x-gsm',
			'wma' => 'audio/x-ms-wma',
			'wax' => 'audio/x-ms-wax',
			'ra' => 'audio/x-realaudio',
			'pls' => 'audio/x-scpls',
			'sd2' => 'audio/x-sd2',
			'wav' => 'audio/x-wav',
			'alc' => 'chemical/x-alchemy',
			'csf' => 'chemical/x-cache-csf',
			'cdx' => 'chemical/x-cdx',
			'cer' => 'chemical/x-cerius',
			'c3d' => 'chemical/x-chem3d',
			'chm' => 'chemical/x-chemdraw',
			'cif' => 'chemical/x-cif',
			'cmdf' => 'chemical/x-cmdf',
			'cml' => 'chemical/x-cml',
			'cpa' => 'chemical/x-compass',
			'bsd' => 'chemical/x-crossfire',
			'ctx' => 'chemical/x-ctx',
			'spc' => 'chemical/x-galactic-spc',
			'cub' => 'chemical/x-gaussian-cube',
			'gal' => 'chemical/x-gaussian-log',
			'gcg' => 'chemical/x-gcg8-sequence',
			'gen' => 'chemical/x-genbank',
			'hin' => 'chemical/x-hin',
			'kin' => 'chemical/x-kinemage',
			'mcm' => 'chemical/x-macmolecule',
			'mol' => 'chemical/x-mdl-molfile',
			'rd' => 'chemical/x-mdl-rdfile',
			'rxn' => 'chemical/x-mdl-rxnfile',
			'tgf' => 'chemical/x-mdl-tgf',
			'mcif' => 'chemical/x-mmcif',
			'mol2' => 'chemical/x-mol2',
			'b' => 'chemical/x-molconn-Z',
			'gpt' => 'chemical/x-mopac-graph',
			'moo' => 'chemical/x-mopac-out',
			'mvb' => 'chemical/x-mopac-vib',
			'asn' => 'chemical/x-ncbi-asn1',
			'ros' => 'chemical/x-rosdal',
			'sw' => 'chemical/x-swissprot',
			'vms' => 'chemical/x-vamas-iso14976',
			'vmd' => 'chemical/x-vmd',
			'xtel' => 'chemical/x-xtel',
			'xyz' => 'chemical/x-xyz',
			'gif' => 'image/gif',
			'ief' => 'image/ief',
			'pcx' => 'image/pcx',
			'png' => 'image/png',
			'wbmp' => 'image/vnd.wap.wbmp',
			'cr2' => 'image/x-canon-cr2',
			'crw' => 'image/x-canon-crw',
			'ras' => 'image/x-cmu-raster',
			'cdr' => 'image/x-coreldraw',
			'pat' => 'image/x-coreldrawpattern',
			'cdt' => 'image/x-coreldrawtemplate',
			'cpt' => 'image/x-corelphotopaint',
			'erf' => 'image/x-epson-erf',
			'ico' => 'image/x-icon',
			'art' => 'image/x-jg',
			'jng' => 'image/x-jng',
			'bmp' => 'image/x-ms-bmp',
			'nef' => 'image/x-nikon-nef',
			'orf' => 'image/x-olympus-orf',
			'psd' => 'image/x-photoshop',
			'pnm' => 'image/x-portable-anymap',
			'pbm' => 'image/x-portable-bitmap',
			'pgm' => 'image/x-portable-graymap',
			'ppm' => 'image/x-portable-pixmap',
			'rgb' => 'image/x-rgb',
			'xbm' => 'image/x-xbitmap',
			'xpm' => 'image/x-xpixmap',
			'xwd' => 'image/x-xwindowdump',
			'eml' => 'message/rfc822',
			'x3dv' => 'model/x3d+vrml',
			'x3d' => 'model/x3d+xml',
			'x3db' => 'model/x3d+binary',
			'manifest' => 'text/cache-manifest',
			'css' => 'text/css',
			'csv' => 'text/csv',
			'323' => 'text/h323',
			'uls' => 'text/iuls',
			'mml' => 'text/mathml',
			'rtx' => 'text/richtext',
			'tsv' => 'text/tab-separated-values',
			'jad' => 'text/vnd.sun.j2me.app-descriptor',
			'wml' => 'text/vnd.wap.wml',
			'wmls' => 'text/vnd.wap.wmlscript',
			'bib' => 'text/x-bibtex',
			'boo' => 'text/x-boo',
			'h' => 'text/x-chdr',
			'htc' => 'text/x-component',
			'csh' => 'text/x-csh',
			'c' => 'text/x-csrc',
			'd' => 'text/x-dsrc',
			'hs' => 'text/x-haskell',
			'java' => 'text/x-java',
			'lhs' => 'text/x-literate-haskell',
			'moc' => 'text/x-moc',
			'gcd' => 'text/x-pcs-gcd',
			'py' => 'text/x-python',
			'scala' => 'text/x-scala',
			'etx' => 'text/x-setext',
			'sh' => 'text/x-sh',
			'vcs' => 'text/x-vcalendar',
			'vcf' => 'text/x-vcard',
			'3gp' => 'video/3gpp',
			'axv' => 'video/annodex',
			'dl' => 'video/dl',
			'fli' => 'video/fli',
			'gl' => 'video/gl',
			'mp4' => 'video/mp4',
			'ogv' => 'video/ogg',
			'mxu' => 'video/vnd.mpegurl',
			'flv' => 'video/x-flv',
			'mng' => 'video/x-mng',
			'wm' => 'video/x-ms-wm',
			'wmv' => 'video/x-ms-wmv',
			'wmx' => 'video/x-ms-wmx',
			'wvx' => 'video/x-ms-wvx',
			'avi' => 'video/x-msvideo',
			'movie' => 'video/x-sgi-movie',
			'ice' => 'x-conference/x-cooltalk',
			'sisx' => 'x-epoc/x-sisx-app',
			'cap' => 'application/cap',
			'pcap' => 'application/cap',
			'nb' => 'application/mathematica',
			'nbp' => 'application/mathematica',
			'doc' => 'application/msword',
			'dot' => 'application/msword',
			'ps' => 'application/postscript',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'epsi' => 'application/postscript',
			'epsf' => 'application/postscript',
			'eps2' => 'application/postscript',
			'eps3' => 'application/postscript',
			'smi' => 'application/smil',
			'smil' => 'application/smil',
			'xhtml' => 'application/xhtml+xml',
			'xht' => 'application/xhtml+xml',
			'xml' => 'application/xml',
			'xsl' => 'application/xml',
			'xsd' => 'application/xml',
			'xls' => 'application/vnd.ms-excel',
			'xlb' => 'application/vnd.ms-excel',
			'xlt' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'pps' => 'application/vnd.ms-powerpoint',
			'cdf' => 'application/x-cdf',
			'cda' => 'application/x-cdf',
			'deb' => 'application/x-debian-package',
			'udeb' => 'application/x-debian-package',
			'dcr' => 'application/x-director',
			'dir' => 'application/x-director',
			'dxr' => 'application/x-director',
			'pfa' => 'application/x-font',
			'pfb' => 'application/x-font',
			'gsf' => 'application/x-font',
			'pcf' => 'application/x-font',
			'pcf.Z' => 'application/x-font',
			'gtar' => 'application/x-gtar',
			'taz' => 'application/x-gtar',
			'phtml' => 'application/x-httpd-php',
			'pht' => 'application/x-httpd-php',
			'ins' => 'application/x-internet-signup',
			'isp' => 'application/x-internet-signup',
			'skp' => 'application/x-koan',
			'skd' => 'application/x-koan',
			'skt' => 'application/x-koan',
			'skm' => 'application/x-koan',
			'kpr' => 'application/x-kpresenter',
			'kpt' => 'application/x-kpresenter',
			'kwd' => 'application/x-kword',
			'kwt' => 'application/x-kword',
			'frm' => 'application/x-maker',
			'maker' => 'application/x-maker',
			'frame' => 'application/x-maker',
			'fm' => 'application/x-maker',
			'fb' => 'application/x-maker',
			'book' => 'application/x-maker',
			'fbdoc' => 'application/x-maker',
			'com' => 'application/x-msdos-program',
			'exe' => 'application/x-msdos-program',
			'bat' => 'application/x-msdos-program',
			'dll' => 'application/x-msdos-program',
			'pac' => 'application/x-ns-proxy-autoconfig',
			'dat' => 'application/x-ns-proxy-autoconfig',
			'pyc' => 'application/x-python-code',
			'pyo' => 'application/x-python-code',
			'qgs' => 'application/x-qgis',
			'shp' => 'application/x-qgis',
			'shx' => 'application/x-qgis',
			'swf' => 'application/x-shockwave-flash',
			'swfl' => 'application/x-shockwave-flash',
			'sit' => 'application/x-stuffit',
			'sitx' => 'application/x-stuffit',
			'texinfo' => 'application/x-texinfo',
			'texi' => 'application/x-texinfo',
			'bak' => 'application/x-trash',
			'old' => 'application/x-trash',
			'sik' => 'application/x-trash',
			't' => 'application/x-troff',
			'tr' => 'application/x-troff',
			'roff' => 'application/x-troff',
			'au' => 'audio/basic',
			'snd' => 'audio/basic',
			'mid' => 'audio/midi',
			'midi' => 'audio/midi',
			'kar' => 'audio/midi',
			'mpga' => 'audio/mpeg',
			'mpega' => 'audio/mpeg',
			'mp2' => 'audio/mpeg',
			'mp3' => 'audio/mpeg',
			'm4a' => 'audio/mpeg',
			'oga' => 'audio/ogg',
			'ogg' => 'audio/ogg',
			'spx' => 'audio/ogg',
			'aif' => 'audio/x-aiff',
			'aiff' => 'audio/x-aiff',
			'aifc' => 'audio/x-aiff',
			'rm' => 'audio/x-pn-realaudio',
			'ram' => 'audio/x-pn-realaudio',
			'cac' => 'chemical/x-cache',
			'cache' => 'chemical/x-cache',
			'cbin' => 'chemical/x-cactvs-binary',
			'cascii' => 'chemical/x-cactvs-binary',
			'ctab' => 'chemical/x-cactvs-binary',
			'csml' => 'chemical/x-csml',
			'csm' => 'chemical/x-csml',
			'cxf' => 'chemical/x-cxf',
			'cef' => 'chemical/x-cxf',
			'emb' => 'chemical/x-embl-dl-nucleotide',
			'embl' => 'chemical/x-embl-dl-nucleotide',
			'inp' => 'chemical/x-gamess-input',
			'gam' => 'chemical/x-gamess-input',
			'gamin' => 'chemical/x-gamess-input',
			'fch' => 'chemical/x-gaussian-checkpoint',
			'fchk' => 'chemical/x-gaussian-checkpoint',
			'gau' => 'chemical/x-gaussian-input',
			'gjc' => 'chemical/x-gaussian-input',
			'gjf' => 'chemical/x-gaussian-input',
			'istr' => 'chemical/x-isostar',
			'ist' => 'chemical/x-isostar',
			'jdx' => 'chemical/x-jcamp-dx',
			'dx' => 'chemical/x-jcamp-dx',
			'mmd' => 'chemical/x-macromodel-input',
			'mmod' => 'chemical/x-macromodel-input',
			'sd' => 'chemical/x-mdl-sdfile',
			'mop' => 'chemical/x-mopac-input',
			'mopcrt' => 'chemical/x-mopac-input',
			'mpc' => 'chemical/x-mopac-input',
			'zmt' => 'chemical/x-mopac-input',
			'prt' => 'chemical/x-ncbi-asn1-ascii',
			'val' => 'chemical/x-ncbi-asn1-binary',
			'aso' => 'chemical/x-ncbi-asn1-binary',
			'pdb' => 'chemical/x-pdb',
			'ent' => 'chemical/x-pdb',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'jpe' => 'image/jpeg',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'djvu' => 'image/vnd.djvu',
			'djv' => 'image/vnd.djvu',
			'igs' => 'model/iges',
			'iges' => 'model/iges',
			'msh' => 'model/mesh',
			'mesh' => 'model/mesh',
			'silo' => 'model/mesh',
			'ics' => 'text/calendar',
			'icz' => 'text/calendar',
			'html' => 'text/html',
			'htm' => 'text/html',
			'shtml' => 'text/html',
			'asc' => 'text/plain',
			'txt' => 'text/plain',
			'text' => 'text/plain',
			'pot' => 'text/plain',
			'brf' => 'text/plain',
			'sct' => 'text/scriptlet',
			'wsc' => 'text/scriptlet',
			'tm' => 'text/texmacs',
			'ts' => 'text/texmacs',
			'h++' => 'text/x-c++hdr',
			'hpp' => 'text/x-c++hdr',
			'hxx' => 'text/x-c++hdr',
			'hh' => 'text/x-c++hdr',
			'c++' => 'text/x-c++src',
			'cpp' => 'text/x-c++src',
			'cxx' => 'text/x-c++src',
			'cc' => 'text/x-c++src',
			'diff' => 'text/x-diff',
			'patch' => 'text/x-diff',
			'p' => 'text/x-pascal',
			'pas' => 'text/x-pascal',
			'pl' => 'text/x-perl',
			'pm' => 'text/x-perl',
			'tcl' => 'text/x-tcl',
			'tk' => 'text/x-tcl',
			'tex' => 'text/x-tex',
			'ltx' => 'text/x-tex',
			'sty' => 'text/x-tex',
			'cls' => 'text/x-tex',
			'dif' => 'video/dv',
			'dv' => 'video/dv',
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
			'lsf' => 'video/x-la-asf',
			'lsx' => 'video/x-la-asf',
			'asf' => 'video/x-ms-asf',
			'asx' => 'video/x-ms-asf',
			'mpv' => 'video/x-matroska',
			'mkv' => 'video/x-matroska',
			'vrm' => 'x-world/x-vrml',
			'vrml' => 'x-world/x-vrml',
			'wrl' => 'x-world/x-vrml'
			);


    /**
     * This is the class constructor
     * 
     * @access public
     * @param array $args - list of properties
     * @return void
     */
	public function __construct(array $args = array()) {

        // set properties if defined
        if (count($args)>0) {
            foreach ($args as $keys => $value)
                self::setVar($keys, $value);
        }

    }


    /**
     * This is the class destructor
     * 
     * @access public
     * @return void
     */
	public function __destruct() {}


    /**
     * This method return the value of a defined class propertie
     * 
     * @access public
     * @param string $keys - the variable name
     * @return mixed
     */
	public function getVar($keys) {
		return isset($this->$keys) ? $this->$keys : Null;
	}


    /**
     * This method set the value of a defined class propertie
     * 
     * @access public
     * @param string $keys - the variable name
     * @param string $value - the variable value
     * @return void
     */
	public function setVar($keys,$value) {

        switch( strtolower($keys) ) {
            case ( $keys == 'couterfile' and (self::isFile($value, True) === True or self::isDir(dirname($value), True) === True) ):
                $this->$keys = $value;
            break;
            case ( $keys == 'statistic' and is_bool($value) === True ):
                $this->$keys = $value;
            break;
        }

	}


    /**
     * This method start a download process
     * 
     * @access public
     * @param string $file - the to downloaded file
     * @return void
     */
	public function getFile($file) {

		if (!file_exists($file))
            throw new Exception('File -'.$file.'- not found!');

		// Must be fresh start
		if(headers_sent())
            throw new Exception('HTTP-Headers was already sent!');

		// Required for some browsers
		if (ini_get('zlib.output_compression'))
			ini_set('zlib.output_compression', 'Off');

		(string) $extension = self::mimetype($file);

		if (!strlen($extension)>0) throw new Exception('File -'.$file.'- has an unkown extension!');
		if (!isset(self::$EXTENSION[$extension])) throw new Exception('File -'.$file.'- has an unkown mimetype!');

        if ($this->statistic === True) {
		    (array) $info = self::readCounter(basename($file));
		    if (self::writeCounter(basename($file), isset($info[0]['count']) ? ($info[0]['count']+1) : 1) === False)
			    throw new Exception("Can't write the download counter file -".$file."-!");
        }

		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers
		header("Content-Type: ".self::$EXTENSION[$extension]);
		header("Content-Disposition: attachment; filename=\"".basename($file)."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($file));
		ob_clean();
		flush();
		readfile($file);
		die();

	}


    /**
     * This method return all files and hits as array
     * or only the hits of a defined file.
     * 
     * @access public
     * @param string $file - the to downloaded file
     * @return mixed
     */
	public function getCounter($file=Null) {

		(string) $name = strlen($file)>0 ? basename($file) : Null;
		(array) $data = self::readCounter($name);
		if (strlen($file)>0) return $data[0]['count'];
		else return $data;

	}


    /**
     * This method return the mimetype of the defined file.
     * 
     * @access private
     * @param string $file - the to downloaded file
     * @return string
     */
	private function mimetype ($file) {

		if (preg_match('/\.(.+)$/', $file, $match)) {
			if (array_key_exists($match[1], self::$EXTENSION)) {
				return $match[1];
			} else {
				if (preg_match('/\.([^\.]+)$/',$match[1], $matches)) {
					return $matches[1];
				} else {
					return $match[1];
				}
			}
		} else {
			return Null;
		}

	}


    /**
     * This method read the counter file and return all files/hits
     * or the defined file/hits as array.
     * 
     * @access private
     * @param string $name - the file name
     * @return array
     */
	private function readCounter($name = Null) {

		(array) $data = array();

		if (!file_exists($this->couterfile)) return array();

		if ( ($fh = fopen($this->couterfile, 'r')) !== False ) {

			// read the text file and loop all lines
			while( (string) $line = @/**/fgets( $fh, filesize($this->couterfile) ) ) {

				// skip blank lines
				if (preg_match('/^$/',$line)) continue;

				// split each line into array by te pipe character
				(array) $value = explode( "|", $line );

				if (strlen($name)>0) {
					if ($name == $value[0]) {
						return array(
								array(
									'name'=>$value[0],
									'count'=>rtrim($value[1])
									)
								);
					}
				} else {
					// get all data set and save it into the $data array
					array_push( $data, array(
								'name'=>$value[0],
								'count'=>rtrim($value[1])
								)
						);
				}

			}

			// close the text file
			fclose($fh);

		}

		// return the $data array of all versions
		return $data;

	}


    /**
     * This method write the hits to a defined file.
     * If no entry of this file exist, a new entry
     * will appent on the counter file.
     * 
     * @access private
     * @param string $name - the file name
     * @param integer $count - the download count
     * @return bool
     */
	private function writeCounter($name, $count) {

		(array) $info = self::readCounter($name);
		(array) $data = self::readCounter();

		if ( ($fh = fopen($this->couterfile, 'w')) !== False ) {

			flock($fh, LOCK_EX);

			if (count($data)>0) {
				if (!isset($info[0]['name'])) {
					array_push($data, array('name'=>$name, 'count'=>1));
				}
				for ( (integer) $i=0; $i<(count($data)); $i++ ) {

					if ( $data[$i]['name'] == $name ) {
						$data[$i]['name'] = $data[$i]['name'];
						$data[$i]['count'] = $count;
					}
					$data[$i]['count'] = str_replace( "\n", "", $data[$i]['count'] );


					(string) $string = $data[$i]['name']."|";
						$string .= $data[$i]['count']."\n";

					fwrite( $fh, $string, strlen($string) );
				}
			} else {
				(string) $string = $name."|";
					$string .= $count."\n";
				fwrite( $fh, $string, strlen($string) );
			}

			flock($fh, LOCK_UN);
			fclose( $fh );
			return True;

		}

		return False;

	}


    /**
     * This method exec a file check
     *
     * @access private
     * @param string $file - path to file
     * @param bool $writable - writable check (default: False)
     * @return bool
     */
    private function isFile( $file,  $writable = False ) {

        clearstatcache();
        return ( is_file($file) && ( $writable ? is_writable($file) : is_readable($file) ) );

    }


    /**
     * This method exec a directory check
     *
     * @access private
     * @param string $file - path to file
     * @param bool $writable - writable check (default: False)
     * @return bool
     */
    private function isDir( $file,  $writable = False ) {

        clearstatcache();
        return ( is_dir($file) && ( $writable ? is_writable($file) : is_readable($file) ) );

    }


};

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker:
// EOF
?>
