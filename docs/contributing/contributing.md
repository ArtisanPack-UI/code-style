---
title: Contributing Guidelines
---

# Contributing to ArtisanPack UI Code Standards

As an open source project, ArtisanPack UI is open to contributions from everyone. You don't need to be a developer to contribute. Whether it's contributing code, writing documentation, testing the package or anything in between, there's a place for you here to contribute.

## Code of Conduct

In order to make this a best place for everyone to contribute, there are some hard and fast rules that everyone needs to abide by.

* ArtisanPack UI is open to everyone no matter your race, ethnicity, gender, who you love, etc. In order to keep it that way, there's zero tolerance for any racist, misogynistic, xenaphobic, bigoted, Zionist, antisemitic (yes, there is a difference), Islamophobic, etc. messages. This includes messages sent to a fellow contributor outside of this repository. In short, don't be a jerk. Failure to comply will result in a ban from the project.
* Be respectful when communicating with fellow contributors.
* Respect the decisions made for what to include in the package.
* Work together to create the best possible code standards package.

## Ways to Contribute

There are many different ways to contribute to ArtisanPack UI Code Standards even if you're not a PHP developer. Here are some (but not all) of the ways you can contribute to the project:

* Write code for the package
* Create new sniffs to extend the functionality
* Test and report bugs found in the package
* Write documentation
* Write tutorials and talk about ArtisanPack UI on your blog and/or social media profiles

## How to File a Bug Report

To file a bug report, please [add a new issue](https://gitlab.com/jacob-martella-web-design/artisanpack-ui/artisanpack-ui-code-style/-/issues/new).

Next, select the bug report template and fill it out as much as you can.

Please add in your environment (operating system, PHP version, etc.) and describe the problem as much as you can. Screenshots and code examples help a ton.

Please select the Awaiting Review milestone and add the necessary labels to the task.

Once you've filled out the issue, you can submit it and it will be reviewed by a maintainer as quickly as possible. Maintainers might ask you questions about the bug, so please be as responsive as possible to help resolve the issue quickly.

## How to File a Feature Request

To file a feature request, please [add a new issue](https://gitlab.com/jacob-martella-web-design/artisanpack-ui/artisanpack-ui-code-style/-/issues/new).

Next, select the feature request template and fill it out as much as you can.

Please describe what you want the feature to be as much as possible and why it should be in the ArtisanPack UI Code Standards package.

Please select the Awaiting Review milestone and add the necessary labels to the task.

Once you've filled out the issue, you can submit it and it will be reviewed by a maintainer as quickly as possible. Maintainers might ask you questions about the feature request, so please be as responsive as possible to make a decision on whether to include it in the package in a timely manner.

NOTE: If your feature request is accepted, your original issue will be closed and transferred to a feature issue.

## Merge Requests

To file a merge request, first make sure that there isn't a merge request that already exists that covers what you're changing.

Next, add a new merge request and select the proper merge request template:

* Bug - For merge requests that fix a bug.
* Feature - For merge requests that merge a new feature into the package.
* Task - For merge requests that complete a task issue.

The release template is only used for package releases and can only be added by maintainers.

Fill out all of the sections of your selected merge request template and submit the request. Your request will need to be reviewed and approved by at least one maintainer.

## Coding Standards

When contributing code to the ArtisanPack UI Code Standards package, please follow the coding standards defined by the package itself. This includes:

* Class names should be in Pascal Case (`ClassName`)
* Function names and variables should be in Camel Case (`functionName`/`variableName`)
* Array keys should be in Camel Case (`$array['arrayKey']`)
* Table columns should be in snake case (`table_column`)

Additionally, make sure your code passes the PHP_CodeSniffer checks using the ArtisanPackUI standard:

```bash
./vendor/bin/phpcs --standard=ArtisanPackUIStandard .
```

## Testing

If you're adding new functionality or fixing a bug, please include tests that cover your changes. This helps ensure that the bug stays fixed and that the new functionality works as expected.

To run the tests:

```bash
./vendor/bin/pest
```

## Documentation

If you're adding new functionality, please also update the documentation to reflect your changes. This includes:

1. Updating the relevant documentation files in the `/docs` directory
2. Adding PHPDoc comments to your code
3. Updating the README.md file if necessary

## Questions?

If you have any questions about contributing to the ArtisanPack UI Code Standards package, please feel free to [open an issue](https://gitlab.com/jacob-martella-web-design/artisanpack-ui/artisanpack-ui-code-style/-/issues/new) with your question.

Thank you for your interest in contributing to ArtisanPack UI Code Standards!