<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Time Tracker</title>
    <script crossorigin src="https://unpkg.com/react@16/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@16/umd/react-dom.development.js"></script>
    <!-- Load Babel Compiler -->
    <script src="https://unpkg.com/babel-standalone@6.26.0/babel.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

</head>

<body>

    <div id='root'></div>

    <script type="text/babel">

        class App extends React.Component {
            state = {
                tasks: []
            }

            componentDidMount() {
                const url = '/backend/tasks.php'
                axios.get(url).then(response => response.data)
                .then((data) => {
                this.setState({ tasks: data })
                console.log(this.state.tasks)
                })
            }

            render() {
                return (
                    <React.Fragment>
                    <h1>Tasks</h1>
                    <table width="100%" >
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Elapsed</th>
                        <th>Created at</th>
                    </tr>

                    {this.state.tasks.map((task) => (
                    <tr>
                        <td>{ task.id }</td>
                        <td>{ task.name }</td>
                        <td>{ task.elapsed }</td>
                        <td>{ task.created_date }</td>
                    </tr>
                    ))}
                    </table>
                    </React.Fragment>
                );
            }
        }

    ReactDOM.render(<App />, document.getElementById('root'));
</script>

</body>

</html>