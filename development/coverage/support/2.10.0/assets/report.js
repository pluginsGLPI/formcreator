var graph = function(classname, method, padding) {
    var g = new dagreD3.graphlib.Graph({ multigraph: true })
        .setGraph({})
        .setDefaultEdgeLabel(function() { return {}; });

    padding = padding || 40;

    g.addNode = function(branch) {
        var opLabel = 'OP #' + branch.op_start,
            lineLabel = 'Line #' + branch.line_start;

        if (branch.op_start != branch.op_end) {
            opLabel = opLabel + ' - #' + branch.op_end;
        }

        if (branch.line_start != branch.line_end) {
            lineLabel = lineLabel + ' - #' + branch.line_end;
        }

        g.setNode(
            branch.op_start,
            {
                label: opLabel + '\n \n' + lineLabel,
                class: branch.hit ? 'covered' : 'uncovered'
            }
        );

        return g;
    };

    g.addEnd = function() {
        g.setNode(
            2147483645,
            {
                label: 'EXIT'
            }
        );

        return g;
    };

    g.addBranch = function(begin, end, hit) {
        var options = {
            class: hit ? 'covered' : 'uncovered',
            lineInterpolate: 'basis',
            labelStyle: 'color: #' + (hit ? '8eff9b' : 'ff8c76'),
            width: padding / 2
        };

        g.setEdge(begin, end, options);

        return g;
    };

    var paths = [];
    g.addPath = function(begin, end, hit) {
        if (!paths[begin]) {
            paths[begin] = [];
        }

        if (!paths[begin][end]) {
            paths[begin][end] = {};
            paths[begin][end][true] = 0;
            paths[begin][end][false] = 0;
        }

        ++paths[begin][end][!!hit];

        return g;
    };

    g.roundNodes = function() {
        g.nodes().forEach(function(v) {
            var node = g.node(v);

            node.rx = node.ry = 5;
        });

        return g;
    };

    g.render = function() {
        var render = new dagreD3.render(),
            svg = d3.select('#' + method + ' svg'),
            group = svg.append("g");

        Object.keys(paths).forEach(function(begin) {
            Object.keys(paths[begin]).forEach(function(end) {
                var covered = paths[begin][end][true],
                    uncovered = paths[begin][end][false],
                    total = covered + uncovered,
                    options = {
                        lineInterpolate: 'basis',
                    };

                if (total > 1) {
                    options.label = ' ' + covered + ' / ' + total;
                }

                options.labelStyle = 'fill: #' + (uncovered === 0 ? '8eff9b' : 'ff8c76');
                options.class = uncovered === 0 ? 'covered' : 'uncovered';

                g.setEdge(begin, end, options);
            });
        });

        render(d3.select('#' + method + ' svg g'), g);

        group.attr("transform", 'translate(' + padding + ', ' + padding + ')');
        svg.attr("height", g.graph().height + (padding * 2));
        svg.attr("width", g.graph().width + (padding * 2));
    };

    return g;
};
