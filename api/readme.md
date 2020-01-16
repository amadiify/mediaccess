
# {$thisProvider->appname} API Overview
### Welcome to the {$this->appname} API.
<br>
<img $src="{$thisProvider->applogo}" alt="App logo" class="img-responsive logo">
<br>

The API is built to allow you to create a functional application or integration quickly and easily. We know from experience - __these are the APIs that power the {$this->appname} application.__ The ecosystem of developers creating integrations on top of the APIs is strong and diverse, ranging from [webinar providers](https://www.moorexa.com) to CRMs to social media.

<br>

All of the {$thisProvider->appname} APIs are organized around REST - if you've interacted with a RESTful API already, many of the concepts will be familiar to you. All API calls to {$thisProvider->appname} should be made to the [{$thisProvider->baseurl}](https://{$this->baseurl}) base domain. We use many standard HTTP features, like HTTP verbs, which can be understood by many HTTP clients. JSON will be returned in all responses from the API, including errors. The APIs are designed to have predictable, straightforward URLs and to use HTTP response codes to indicate API errors.

<br>