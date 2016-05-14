# Bit.ly URL cleaner and shortener

## Purpose

The purpose of this code is to take a list of shortened bit.ly or other redirected URLs, lengthen them, chop off any existing UTM codes and other query parameters to produce a clean URL, then append new UTM codes to it and re-shorten with bit.ly. The output is a CSV, pipe-delimited, which you can then import into spreadsheets, databases, etc.

## Prerequisites

The following software must be installed:
- PHP 5.6 or 7.0
- cUrl and php-curl command line tools
- a free developer API key from bit.ly
- Read/write access to the local disk

You should also have a working knowledge of UTM codes and how they apply to Google Analytics.

## Why does this exist?

I ran into an attribution problem when I put together my newsletter. I was re-using links I had shared on social media with tools like Buffer, which encode their own UTM tracking codes. When I re-used those URLs, I was destroying any chance I had of tracking and attributing my email marketing performance in my Google Analytics.

This script takes those shortened URLs, lengthens them, chops off the previous UTM parameters (and any other query parameters), and produces a long clean link. It then appends UTM codes of our choosing to the URL, and re-shortens it to look nice for email marketing.

## Warranty and Disclaimer

This software comes with absolutely no warranty whatsoever. You accept all risk when you download and install it. No support is provided, either. You’re on your own.

## License

This software is licensed under the GNU General Public License, version 3.0. More information can be found in the LICENSE.md file included with this distribution.