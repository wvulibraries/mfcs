Sets  URL for an IDNO in the database. The URL provided should be a public accessible URL. This is the URL that will be exported to the OAI application.

query string:

* id : string, IDNO for an object
* url : url encoded string. Valid url, including protocol.

Returns JSON.

{
  error : boolean;
  message : string;
}
