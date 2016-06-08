# canvas-php-curl
This is a simplistic class designed specifically for use with Instructure's Canvas LMS.

[**Canvas LMS - REST API and Extensions Documentation**](https://canvas.instructure.com/doc/api/index.html)

#### Basic Information:
 - Uses supplied domain name to build a base URL for calls
 - GET, PUT, and POST statements
 - Supplied data queries are only acceptable in associative arrays which can be directly converted to match the API
 - GET statements will automatically retrieve a complete list of results, not the standard first 10 results or the standard max results of the first 100 results
 - Returned values are in the format of an array containing the compiled JSON decoded results

#### To-Do
 - Currently ignores results with a 302 and 404 response codes
 - Additional testing and functinality refinement

# How-To Use
#### Using a Base URL
A base URL would be the portion of the URL that is the same for all cURLs that will be executed.
``` php
<?php
  // Update to reflect the token of the admin user that is to be used
  $token = "Authorization: Bearer 1234~8ca7f0dc11599a193be27500387156b982e53d7a180973cc33c8c159a62c1373";
  // Update to reflect the address to your institute
  $site = "canvas.instructure.com";

  require "class.curl.php";

  $cURL = new Curl($token, $site);
  $cURL->closeCurl();
?>
```

#### GETting Information
Retrieving information only requires the target URL, however, an associative array can be provided, in accordance with the API, to filter results.
``` php
<?php
  // Update to reflect the token of the admin user that is to be used
  $token = "Authorization: Bearer 1234~8ca7f0dc11599a193be27500387156b982e53d7a180973cc33c8c159a62c1373";
  // Update to reflect the address to your institute
  $site = "canvas.instructure.com";

  require "class.curl.php"

  $cURL = new Curl($token, $site);

  $coursepages = $cURL->get("/courses/101/pages");
  $coursepagesSorted = $cURL->get("/courses/101/pages", array(
                                                          "sort" => "title",
                                                          "order" => "desc"
                                                        )
                              );

  $cURL->closeCurl();
?>
```

#### PUTting Information
Unlike when retrieving information, submitting information via **PUT** requires data to be included in the command.
``` php
<?php
  // Update to reflect the token of the admin user that is to be used
  $token = "Authorization: Bearer 1234~8ca7f0dc11599a193be27500387156b982e53d7a180973cc33c8c159a62c1373";
  // Update to reflect the address to your institute
  $site = "canvas.instructure.com";

  require "class.curl.php"

  $cURL = new Curl($token, $site);

  $coursepagesSorted = $cURL->put("/users/101", array(
                                                  "user" => array(
                                                    "name" => "Papa Giorgio",
                                                    "short_name" => "Papa",
                                                    "sortable_name" => "Giorgio, Papa")
                                                  )
                              );

  $cURL->closeCurl();
?>
```

#### POSTing Information
Like when using **PUT**, **POST** must have data submitted with it.
``` php
<?php
  // Update to reflect the token of the admin user that is to be used
  $token = "Authorization: Bearer 1234~8ca7f0dc11599a193be27500387156b982e53d7a180973cc33c8c159a62c1373";
  // Update to reflect the address to your institute
  $site = "canvas.instructure.com";

  require "class.curl.php"

  $cURL = new Curl($token, $site);

  $coursepagesSorted = $cURL->put("/accounts/1/users", array(
                                                         "user" => array(
                                                                     "name" => "Papa Giorgio",
                                                                     "short_name" => "Papa",
                                                                     "sortable_name" => "Giorgio, Papa",
                                                                     "skip_registration" => true
                                                         ),
                                                         "pseudonym" => array(
                                                                          "unique_id" => "pgiorgio",
                                                                          "password" => "P@s$w04d",
                                                                          "send_confirmation" => false
                                                         ),
                                                         "communication_channel" => array(
                                                                                      "type" => "email",
                                                                                      "address" => "pgiorgio@gmail.com",
                                                                                      "skip_confirmation" => true
                                                         )
                                                       )
                              );

  $cURL->closeCurl();
?>
```
