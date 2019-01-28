# php-vue-to-twig

[![Build Status](https://travis-ci.org/Paneon/php-vue-to-twig.svg?branch=master)](https://travis-ci.org/Paneon/php-vue-to-twig)

Compile vue files to twig templates with PHP

## Directives

|Directive|Implemented|
|---------|:---------:|
|v-text||
|v-html||
|v-show|:white_check_mark:|
|v-if|:white_check_mark:|
|v-else|:white_check_mark:|
|v-else-if|:white_check_mark:|
|v-for|:white_check_mark:|
|v-on|:white_check_mark:|
|v-bind|partially working|
|v-bind:style|partially working|
|v-bind:class|partially working|
|v-model||
|v-pre||
|v-cloak||
|v-once||


## Other Functionalities

|Functionality|Implemented|
|:------------|:---------:|
|Slots||
|Components|:white_check_mark:|
|Filters||


## Limitations

It's difficult to interpret JavaScript language features and translate them into twig.

For example string concatenation inside attribute binding does not work currently: :no_entry_sign:

```vue
<div :style="{ fontSize: size + 'px' }"></div> 
```

But if you move this into a single property like (A) or (B), it will work.

```vue
<!-- (A) -->
<div :style="divStyleProperty"></div> 

<!-- (B) -->
<div :style="{ fontSize: fontSizeVariable }"></div> 
```
