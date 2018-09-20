module.exports = {
	"parser": "babel-eslint",
    "env": {
		"browser": true,
        "es6": true,
        "node": true,
		"jquery": true
    },
    "extends": ["eslint:recommended", 'plugin:react/recommended'],
    "parserOptions": {
        "sourceType": "module"
    },
    "rules": {
        "linebreak-style": [
            "error",
            "unix"
        ],
        "quotes": [
            "error",
            "single"
        ],
        "semi": [
            "error",
            "always"
        ]
    }
};