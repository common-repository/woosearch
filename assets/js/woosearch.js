'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol ? "symbol" : typeof obj; };

//需參考 https://github.com/vakhtang/react-search-bar/blob/master/LICENSE
//https://github.com/vakhtang/react-search-bar/blob/master/src/SearchBar.jsx

/*!
 *  react-search-bar 1.1.4 by @vakhtang - https://github.com/vakhtang/react-search-bar
 *  License - https://github.com/vakhtang/react-search-bar/blob/master/LICENSE (The MIT License)
 */
var keyCodes = {
    ENTER: 13,
    ESCAPE: 27,
    UP: 38,
    DOWN: 40
};

var WooSearchBar = React.createClass({
    propTypes: {
        delay: React.PropTypes.number,
        initPlaceholders: React.PropTypes.func,
        onSearch: React.PropTypes.func,
        inputPlaceholder: React.PropTypes.string,
        loadPreview: React.PropTypes.func
    },
    getDefaultProps: function getDefaultProps() {
        return {
            placeholders: [],
            delay: 200,
            initPlaceholders: function initPlaceholders() {
                return [];
            },
            onSearch: function onSearch() {},
            inputPlaceholder: '',
            loadPreview: function loadPreview(value) {
                return new Promise(function (fulfill, reject) {
                    fulfill([]);
                });
            }
        };
    },
    displayName: 'WooSearchBar',
    scroll: function scroll(key) {
        var _state = this.state;
        var currentHighlightedIndex = _state.highlightedItem;
        var placeholders = _state.placeholders;
        var previewItems = _state.previewItems;
        var searchValue = _state.searchValue;

        var itemList = [];
        if (placeholders.length > 0) {
            itemList = placeholders;
        } else if (previewItems.length > 0) {
            itemList = previewItems;
        }
        var lastItemIndex = itemList.length - 1;
        var nextItemIndex = void 0;

        if (key === keyCodes.UP) {
            nextItemIndex = currentHighlightedIndex <= 0 ? lastItemIndex : currentHighlightedIndex - 1;
        } else {
            nextItemIndex = currentHighlightedIndex === lastItemIndex ? 0 : currentHighlightedIndex + 1;
        }

        var newSearchValue = placeholders.length > 0 ? placeholders[nextItemIndex].placeholder : searchValue;

        this.setState({
            highlightedItem: nextItemIndex,
            searchValue: newSearchValue
        });
    },
    getInitialState: function getInitialState() {
        return {
            searchValue: '',
            placeholders: [],
            highlightedItem: -1,
            previewItems: []
        };
    },
    updatePreviewItems: function updatePreviewItems(previewItems) {
        this.setState({ previewItems: previewItems });
    },
    handleChange: function handleChange(e) {
        var _this = this;

        clearTimeout(this.timer);
        var input = e.target.value;
        if (!input) return this.setState(this.getInitialState());
        this.setState({ searchValue: input, placeholders: [] });
        this.timer = setTimeout(function () {
            _this.props.loadPreview(input).then(_this.updatePreviewItems);
        }, this.props.delay);
    },
    handleFocus: function handleFocus() {
        if (!this.state.searchValue) {
            this.setState({ placeholders: this.props.initPlaceholders() });
            this.setState({ previewItems: [] });
        } else {
            this.props.loadPreview(this.state.searchValue).then(this.updatePreviewItems);
        }
    },
    handleBlur: function handleBlur() {
        this.setState({ placeholders: [], previewItems: [] });
    },
    handleKeyDown: function handleKeyDown(e) {
        var key = e.which || e.keyCode;
        switch (key) {
            case keyCodes.UP:
            case keyCodes.DOWN:
                e.preventDefault();
                this.scroll(key);
                break;

            case keyCodes.ENTER:
                this.search();
                break;

            case keyCodes.ESCAPE:
                this.handleBlur();
                this.refs.input.blur();
                break;
        }
    },
    normalizeInput: function normalizeInput() {
        return this.state.searchValue.toLowerCase().trim();
    },
    search: function search() {
        if (!this.state.searchValue) return;
        var value = this.normalizeInput();
        clearTimeout(this.timer);
        this.refs.input.blur();

        var _getInitialState = this.getInitialState();

        var highlightedItem = _getInitialState.highlightedItem;
        var placeholders = _getInitialState.placeholders;
        var previewItems = _getInitialState.previewItems;

        this.setState({ highlightedItem: highlightedItem, placeholders: placeholders, previewItems: previewItems });
        if (this.props.onSearch) {
            this.props.onSearch(value);
        }
    },
    onSelection: function onSelection(placeholder) {
        var _this2 = this;

        this.setState({ searchValue: placeholder }, function () {
            return _this2.search();
        });
    },

    onSearch: function onSearch(e) {
        e.preventDefault();
        this.search();
    },
    render: function render() {
        return React.createElement(
            'div',
            { className: 'search-bar-wrapper' },
            React.createElement(
                'div',
                { className: 'search-bar-field' },
                React.createElement('input', {
                    className: 'search-bar-input',
                    type: 'text',
                    ref: 'input',
                    placeholder: this.props.inputPlaceholder,
                    value: this.state.searchValue,
                    onChange: this.handleChange,
                    onFocus: this.handleFocus,
                    onBlur: this.handleBlur,
                    onKeyDown: this.handleKeyDown
                }),
                React.createElement('input', {
                    className: 'icon search-bar-submit',
                    type: 'submit',
                    onClick: this.onSearch })
            ),
            this.state.placeholders.length > 0 && React.createElement(Placeholders, {
                placeholders: this.state.placeholders,
                highlightedItem: this.state.highlightedItem,
                onSelection: this.onSelection
            }),
            this.state.previewItems.length > 0 && React.createElement(PreviewItems, {
                previewItems: this.state.previewItems,
                highlightedItem: this.state.highlightedItem
            })
        );
    }
});

var renderWooSearchBar = function renderWooSearchBar(params) {
    ReactDOM.render(React.createElement(WooSearchBar, params), document.getElementById(params.renderId));
};

var Placeholders = React.createClass({
    displayName: 'Placeholders',

    propTypes: {
        highlightedItem: React.PropTypes.number,
        placeholders: React.PropTypes.array.isRequired
    },
    getDefaultProps: function getDefaultProps() {
        return {
            placeholders: [],
            highlightedItem: -1
        };
    },
    getInitialState: function getInitialState() {
        return {
            activeItem: -1
        };
    },
    render: function render() {
        var _this3 = this;

        var _props = this.props;
        var highlightedItem = _props.highlightedItem;
        var placeholders = _props.placeholders;
        var activeItem = this.state.activeItem;

        return React.createElement(
            'ul',
            { className: 'search-bar-placeholders',
                onMouseLeave: function onMouseLeave() {
                    return _this3.setState({ activeItem: -1 });
                } },
            placeholders.map(function (placeholder, index) {
                var displayClassName = highlightedItem === index || activeItem === index ? 'highlighted' : '';
                return React.createElement(
                    'li',
                    {
                        key: index,
                        className: displayClassName,
                        onClick: function onClick() {
                            return _this3.props.onSelection(placeholder.placeholder);
                        },
                        onMouseDown: function onMouseDown(e) {
                            return e.preventDefault();
                        },
                        onMouseEnter: function onMouseEnter() {
                            return _this3.setState({ activeItem: index });
                        } },
                    React.createElement(
                        'span',
                        null,
                        React.createElement('img', { src: placeholder.imgUrl }),
                        React.createElement(
                            'strong',
                            null,
                            placeholder.placeholder
                        )
                    )
                );
            })
        );
    }
});
var PreviewItems = React.createClass({
    displayName: 'PreviewItems',

    propTypes: {
        highlightedItem: React.PropTypes.number,
        previewItems: React.PropTypes.array.isRequired
    },
    getDefaultProps: function getDefaultProps() {
        return {
            previewItems: [],
            highlightedItem: -1
        };
    },
    getInitialState: function getInitialState() {
        return {
            activeItem: -1
        };
    },
    render: function render() {
        var _this4 = this;

        var _props2 = this.props;
        var highlightedItem = _props2.highlightedItem;
        var previewItems = _props2.previewItems;
        var activeItem = this.state.activeItem;

        return React.createElement(
            'ul',
            { className: 'search-bar-previewItems',
                onMouseLeave: function onMouseLeave() {
                    return _this4.setState({ activeItem: -1 });
                } },
            previewItems.map(function (previewItem, index) {
                var displayClassName = highlightedItem === index || activeItem === index ? 'highlighted' : '';
                return React.createElement(
                    'li',
                    {
                        key: index,
                        className: displayClassName,
                        onClick: function onClick() {
                            return window.location.href = previewItem.productUrl;
                        },
                        onMouseDown: function onMouseDown(e) {
                            return e.preventDefault();
                        },
                        onMouseEnter: function onMouseEnter() {
                            return _this4.setState({ activeItem: index });
                        } },
                    React.createElement(
                        'span',
                        null,
                        React.createElement('img', { src: previewItem.productImgUrl }),
                        React.createElement(
                            'div',
                            { className: 'r-preview-item-detail' },
                            React.createElement(
                                'strong',
                                null,
                                previewItem.title
                            ),
                            React.createElement(
                                'div',
                                { className: 'price-detail-box' },
                                React.createElement('div', { dangerouslySetInnerHTML: { __html: previewItem.priceHtml } }),
                                React.createElement(
                                    'p',
                                    null,
                                    previewItem.description
                                )
                            )
                        )
                    )
                );
            })
        );
    }
});

(function (root, factory) {
    root.woosearch = factory();
})(window || global || undefined, function () {
    'use strict';

    var defaultParams = {
        renderId: 'woosearch-content',
        placeholders: [],
        delay: 200,
        initPlaceholders: function initPlaceholders() {
            return [];
        },
        onSearch: function onSearch() {},
        inputPlaceholder: '',
        loadPreview: function loadPreview() {}
    };

    var extend = function extend(a, b) {
        for (var key in b) {
            if (b.hasOwnProperty(key)) {
                a[key] = b[key];
            }
        }
        return a;
    };

    function woosearch() {
        var args = arguments;

        return modalDependant.apply(this, args);
    }

    var modalParams = extend({}, defaultParams);

    function modalDependant() {
        var params = extend({}, modalParams);

        switch (_typeof(arguments[0])) {
            case 'undefined':
                break;
            case 'object':
                extend(params, arguments[0]);
                break;

            default:
                console.error('Unexpected type of argument! Expected "object", got ' + _typeof(arguments[0]));
                return false;
        }

        if (_typeof(params.renderId) === undefined || !document.getElementById(params.renderId)) {
            console.error('WooSearch does not find element! Please check "renderId" in element.');
            return false;
        }

        setParameters(params);
    }

    var setParameters = function setParameters(params) {
        renderWooSearchBar(params);
    };

    window.woosearch = window.ws = woosearch;

    return woosearch;
});
