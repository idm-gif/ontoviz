# CLAUDE.md — Ontoviz

## Project Overview

Ontoviz is a single-page XML ontology visualizer. It parses XML files describing ontological structures (nodes, edges, data fields) and renders an interactive, navigable tree UI with filtering, search, and media display. The app is primarily Ukrainian-language.

## Architecture

**Single-file frontend**: The entire UI lives in `index.html` — HTML, CSS, and vanilla JavaScript in one file. No build tools, bundlers, or frameworks.

**Backend (optional)**: `api.php` provides server-side file listing, serving, upload/delete (auth-protected), and a proxy to the Polyhedron API (`polyhedron.ulif.org.ua`). The frontend gracefully falls back to static `xml/index.json` if PHP is unavailable.

## File Structure

```
index.html       — Complete SPA (styles + markup + JS)
api.php          — PHP backend API (list, get, upload, delete, proxy, login)
xml/             — Directory for XML ontology files
xml/index.json   — Static fallback file list (when PHP unavailable)
```

## Key Concepts

- **Nodes**: XML `<Node>` elements with `guid`, `nodeName`, `nclass`, and child `<data>` elements
- **Edges**: XML `<Edge>` elements linking nodes via `node1`/`node2` attributes (parent-child)
- **Root node**: First node without a parent; used as navigation entry point
- **nclass**: Node classification, used for filtering groups
- **Data fields**: Key-value pairs on nodes; some are media (images/video detected by label or file extension)

## UI Structure

1. **Input screen**: File selection (server files, local upload, Polyhedron API URL)
2. **App view**:
   - **Navbar**: Home button, top-level node links, share button, external link
   - **Breadcrumb bar**: Labeled "Структура онтосайту", shows node ancestry path
   - **Search + filter row**: Search input and filter button on one line
   - **Filter sidebar**: Slide-out panel with auto-generated categorical/numerical/date filters
   - **Main display**: Node detail view (media, data fields grid, children grid) or search results

## CSS Conventions

- Uses [Pico CSS v2](https://picocss.com/) as base framework
- Custom properties in `:root` for sizing (`--pico-font-size`, `--sidebar-width`, `--max-content-width`)
- Layout optimized for Full HD / HD monitors (`--max-content-width: 1600px`)
- BEM-like class naming: `.child-card`, `.child-thumb-wrap`, `.data-card-inline`
- `.hidden` utility class for toggling visibility

## JavaScript Patterns

- All state in module-level variables: `allNodes` (Map), `rootNode`, `currentNode`, `activeFilters`, `searchTerm`
- DOM manipulation via vanilla JS (`createElement`, `innerHTML`)
- Navigation: `navigateTo(node)` updates breadcrumbs, renders display, updates URL
- Filtering: three types — categorical (checkboxes), numerical (min/max), chronological (date range)
- URL sharing: query params `?file=`, `?source=polyhedron&type=&id=`, `?node=guid`

## Development Notes

- No build step — edit `index.html` directly and reload
- Test by opening `index.html` in a browser (static mode) or serving via PHP
- PHP backend requires `users.php` file for authentication (not in repo)
- Media detection: `isImg()`, `isVideo()`, `isMedia()` check data label keywords and file extensions
- The carousel and primary image share a max-width of 400px for compact display
- Node detail view does NOT show a separate title header — the node name appears only in the breadcrumb to avoid duplication

## API Endpoints (api.php)

| Action | Method | Params | Auth |
|--------|--------|--------|------|
| `list` | GET | — | No |
| `get` | GET | `file` | No |
| `proxy` | GET | `type` (fname/sharedgraph), `id` | No |
| `login` | POST | JSON `{user, pass}` | No |
| `upload` | POST | `xml_file` (multipart) | Yes |
| `delete` | POST | JSON `{filename}` | Yes |

## Style Guidelines

- Keep everything in `index.html` — no separate JS/CSS files
- Use Ukrainian for all user-facing text
- Maintain compact sizing for HD/Full HD displays
- Prefer CSS custom properties for reusable values
