# POST-Printing


## Features

* supports  printing to 1-16 print stations having CARD and/or LABEL printers
* can be used to print with "real" OR self-issued certificates ( e.g. XHR/POST out of web apps will  work )
* auto-detects longest edge and rotates given PDF's properly
* provides a management page that features:
   * printer state ( OK ,FAILING )
   * routing printer from client N to client M in maintenance phases
   * rotation / medium type setting per evolis primacy printer

* includes a print-dummy.php that returns the pdf to check of you send correct data
* accepts the following fields: 
   * 'client' => $client,
   * 'type' => 'label' OR 'card' OR 'receipt' 
   * 'file' => base64 encoded PDF ( or raw text on type=receipt )
* accepts Content-type: 
    * application/x-www-form-urlencoded 
    * application/json

* accepts JSON text to print receipts 

```
curl --header "Content-Type: application/json"   --request POST   --data '{"client": 1,"type":"receipt","file":"ASimpleText\nAndANewLine\nAndTheAnd"}'   http://localhost/print.php

```

## Usage

* examples are in `tests/` folder,

  * `post-send-card.php` -> sends a test pdf ( you might try `post-send-card.php  card.pdf` )
  * `post-send-card.php` -> sends a test pdf ( you might try `post-send-label.php label.pdf` )
  * `send_json.sh` -> the aforementioned curl request to print raw text via label printers 

* for evolis: first PDF site is front , second is back side 

> ## installing ( best in byobu|screen|tmux .. )

>>## deploy standard ubuntu22.04 (we used mini iso/tftp)

>>>sudo apt-get install git

>>>git clone https://github.com/benchonaut/POST-Printing.git
>>>cd POST-Printing/install
>>>sudo bash 0_install.sh;sudo reboot 


---

### TREE ( may change ) :

```


.
├── client_live_os
│   ├── livecd-seed
│   │   ├── etc
│   │   │   ├── fix-mouse-key.sh
│   │   │   ├── get-printer-status-OLD.sh
│   │   │   ├── get-printer-status.sh
│   │   │   └── rc.local.real
│   │   ├── root
│   │   └── usr
│   │       └── share
│   │           └── wallpapers
│   │               └── slax_wallpaper.jpg
│   ├── livecd-setup.sh
│   └── server-side-preparation.sh
├── install
│   ├── 0_1_default-branding.sh
│   ├── 0_install.sh
│   ├── assets
│   │   ├── favicon.tgz
│   │   └── supervisor-cups-notification.ini
│   ├── brother_ql
│   ├── client-side
│   │   └── get-printer-status.sh
│   ├── drivers
│   │   ├── brother_lpdwrapper_ql720nw
│   │   ├── CARD01.ppd.base64
│   │   ├── CARD01.ppd.O.base64
│   │   ├── evolis-primacyE.ppd.gz
│   │   ├── evorasterizer
│   │   ├── LABEL01.ppd.base64
│   │   ├── LABEL01.ppd.O.base64
│   │   ├── ql720nwcupswrapper-1.1.4-0.i386.deb
│   │   ├── ql720nwlpr-1.1.4-0.i386.deb
│   │   ├── ql720nw.tgz
│   │   ├── stamp.card
│   │   ├── stamp.header
│   │   └── stamp.label
│   ├── nginx-config
│   │   └── default
│   ├── scripts
│   │   ├── printer_clean_tmp.sh
│   │   └── printer_status.sh
│   └── webroot
│       ├── cups-get-id.php
│       ├── cups-status.php
│       ├── index.html
│       ├── print.php
│       ├── route-status.php
│       └── setup
│           └── router.php
├── README.md
└── tests
    ├── card-2side_merged.xcf.tgz
    ├── card.pdf
    ├── card-portrait-2side_merged.xcf.tgz
    ├── card-portrait.pdf
    ├── card-portrait.xcf
    ├── card.xcf
    ├── label.pdf
    ├── label.xcf
    ├── pagesize_pdfinfo.php
    ├── post-send-card.php
    ├── post-send-label.php
    ├── print-dummy.php
    ├── send-json.sh
    └── show-results.php

17 directories, 49 files
```
