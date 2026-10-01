# -*- coding: utf-8 -*-
from pathlib import Path

OLD = (
    '<option value="{{ $port->port_id }}" data-port-id="{{ $port->port_id }}" '
    'data-city-id="{{ $port->city_id }}" data-type="port" data-port-kind="{{ $port->type }}" '
    'data-country="{{ $port->country }}">{{ $port->port_name }} ({{ $port->type }})</option>'
)
NEW = (
    '<option value="{{ $port->port_id }}" data-name="{{ $port->port_name }}" data-port-id="{{ $port->port_id }}" '
    'data-city-id="{{ $port->city_id }}" data-type="port" data-port-kind="{{ $port->type }}" '
    'data-country="{{ $port->country }}">{{ $port->port_name }} ({{ $port->type }})</option>'
)

for fn in ["create.blade.php", "edit.blade.php"]:
    path = Path(r"c:/xampp/htdocs/Azure_new_files/resources/views/enquiryform_pro") / fn
    text = path.read_text(encoding="utf-8")
    c = text.count(OLD)
    # create arrival already has data-name
    text2 = text.replace(OLD, NEW)
    path.write_text(text2, encoding="utf-8")
    print(fn, "replaced", c, "remaining without data-name", text2.count(OLD))

# verify jqPort
for fn in ["create.blade.php", "edit.blade.php"]:
    path = Path(r"c:/xampp/htdocs/Azure_new_files/resources/views/enquiryform_pro") / fn
    text = path.read_text(encoding="utf-8")
    assert "var jqPort = jQuery(el);" in text, fn
    assert "const  = jQuery(el);" not in text, fn + " still broken"
    print(fn, "jqPort OK")
