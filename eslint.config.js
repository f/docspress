export default [
  {
    ignores: ["dist/**", "coverage/**", "node_modules/**"]
  },
  {
    files: ["src/**/*.js", "test/**/*.js", "scripts/**/*.mjs"],
    languageOptions: {
      ecmaVersion: 2023,
      sourceType: "module",
      globals: {
        Buffer: "readonly",
        URL: "readonly",
        URLSearchParams: "readonly",
        console: "readonly",
        fetch: "readonly",
        setTimeout: "readonly",
        process: "readonly"
      }
    },
    rules: {
      "no-unused-vars": ["error", { "argsIgnorePattern": "^_" }],
      "no-undef": "error",
      "no-console": "off"
    }
  }
];
