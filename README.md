# phpporscheconnectapi
A php translation of pyporscheconnectapi by [Johan Isacsson](https://github.com/CJNE) with some added modularity.

*NOTE:* This work is not officially supported by Porsche and functionality can stop working at any time without warning

## Usage

Install Guzzle (v7) via composer in the root directory
Example is included with mainly Porsche Taycan support, but you can var_dump your way through other cars variables if you have access to them in your account. Multiple car support is included, but commented out.

I recommend using chained async calls as opposed to how it's done in the included .class file. This is because Porsches servers are... slow. Better to use that time to get everything at once.


## Credits
[Johan Isacsson](https://github.com/CJNE) for creating the authentication flow and the original Python Library [pyporscheconnectapi](https://github.com/CJNE/pyporscheconnectapi)


