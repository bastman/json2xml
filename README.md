json2xml
========

Serialize to xml

(uses core logic of zend-rpcxml client)

serializer protocol: http://en.wikipedia.org/wiki/XML-RPC

Experimental! (quick-n-dirty prove of concept)

Examples:
=========

cd example/teststack/TestStackExample/bin

List Tasks:
php runtask.php Json2Xml


RESULT
======

<pre>
json {"fooInt":1234567890,"fooBool":true,"fooString":"bar","fooArrayList":[1,2,3,4],"fooArrayDictionary":{"foo":"bar","nestedDict":{"foo":"bar"}}}
</pre>

```xml

xml: "<value><struct><member><name>fooInt</name><value><int>1234567890</int></value></member><member><name>fooBool</name><value><boolean>1</boolean></value></member><member><name>fooString</name><value><string>bar</string></value></member><member><name>fooArrayList</name><value><array><data><value><int>1</int></value><value><int>2</int></value><value><int>3</int></value><value><int>4</int></value></data></array></value></member><member><name>fooArrayDictionary</name><value><struct><member><name>foo</name><value><string>bar</string></value></member><member><name>nestedDict</name><value><struct><member><name>foo</name><value><string>bar</string></value></member></struct></value></member></struct></value></member></struct></value>"

```