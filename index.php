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
    <!-- Libraries -->
    <script src="https://unpkg.com/moment@2.24.0/moment.js"></script>
    <script src="https://unpkg.com/moment-duration-format@2.3.2/lib/moment-duration-format.js"></script>
</head>

<body>

    <div id='root'></div>

    <script type="text/babel">

        class App extends React.Component {
            state = {
                isRunning: false,
                elapsed: 0,
                interval: null,
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

            addSecond() {
                this.setState({
                    elapsed: this.state.elapsed + 1
                });
            }

            startTimer() {
                const interval = setInterval(() => this.addSecond(), 1000);
                this.setState({
                    isRunning: true,
                    interval
                });
            }
            stopTimer() {
                clearInterval(this.state.interval);
                this.setState({ 
                    isRunning: false,
                    interval: null
                });
            }
            resetTimer() {
                clearInterval(this.state.interval);
                this.setState({
                    isRunning: false,
                    interval: null,
                    elapsed: 0
                });
            }

            render() {
                return (
                    <React.Fragment>
                    <div>
                        <h1>Time Tracker</h1>
                        <h4>Control the time you invest in each daily tasks</h4>
                    </div>
                    <div>
                        <div>
                            <h2>Timer: { moment.duration(this.state.elapsed, 'seconds').format("H:mm:ss") }</h2>
                            { this.state.isRunning ? 
                                (<button onClick={() => this.stopTimer()}>Stop</button>) 
                                : (<button onClick={() => this.startTimer()}>Start</button>) 
                            }
                        </div>  
                        <div>
                            <h2>Tasks</h2>
                            <table border="true" width="100%" style={{ border: '1px', borderColor: 'red' }}>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Elapsed</th>
                                    <th>Created at</th>
                                </tr>
                            </thead>

                            <tbody>
                                {this.state.tasks.map((task, i) => (
                                <tr key={`task-${task.id}`}>
                                    <td style={{ textAlign: 'center' }}>{ task.id }</td>
                                    <td style={{ textAlign: 'center' }}>{ task.name }</td>
                                    <td style={{ textAlign: 'center' }}>{ moment.duration(task.elapsed, 'seconds').format("H:mm:ss") }</td>
                                    <td style={{ textAlign: 'center' }}>{ moment.unix(task.created_date).format("LLL") }</td>
                                </tr>
                                ))}
                            </tbody>

                            </table>                    
                        </div>
                    </div>
                    

                    </React.Fragment>
                );
            }
        }

    ReactDOM.render(<App />, document.getElementById('root'));
</script>

</body>

</html>