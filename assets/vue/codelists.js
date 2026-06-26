/**
 * Shared codelist cache.
 *
 * Hits the WP-LKOD plugin's native REST API:
 *   GET /wp-json/wp-lkod/v1/publishers          -> [{key, label}]
 *   GET /wp-json/wp-lkod/v1/codelists/<name>    -> [{key, label}]
 *
 * Each method returns a Promise resolving to { items, map }:
 *   items: original array
 *   map:   { [iri]: label }
 *
 * Results are cached on the URL so parallel callers share one request.
 */
window.LkodCodelists = (function () {
  'use strict';

  const _cache = {};

  function _root() {
    return (window.LKOD_CONFIG && window.LKOD_CONFIG.pluginApi) || '';
  }

  function _get(path) {
    const url = _root() + path;
    if (!_cache[url]) {
      _cache[url] = fetch(url)
        .then(function (r) { return r.ok ? r.json() : []; })
        .then(function (data) {
          const items = Array.isArray(data) ? data : [];
          const map = {};
          items.forEach(function (it) {
            if (it && it.key) map[it.key] = it.label || it.key;
          });
          return { items: items, map: map };
        })
        .catch(function () { return { items: [], map: {} }; });
    }
    return _cache[url];
  }

  return {
    publishers:   function () { return _get('publishers'); },
    theme:        function () { return _get('codelists/dataset-theme'); },
    frequency:    function () { return _get('codelists/frequency'); },
    license:      function () { return _get('codelists/license'); },
    personalData: function () { return _get('codelists/personal-data'); },
    format:       function () { return _get('codelists/format'); },
    mediaType:    function () { return _get('codelists/media-type'); },
    spatial:      function () { return _get('codelists/spatial'); },
    hvdCategory:  function () { return _get('codelists/hvd-category'); },
    datasetType:  function () { return _get('codelists/dataset-type'); },

    /** Look up an IRI in a map, falling back to the IRI's last segment */
    resolve: function (map, iri, fallback) {
      if (!iri) return fallback || '';
      if (map && map[iri]) return map[iri];
      return fallback || (iri.split('/').pop()) || iri;
    },
  };
}());
