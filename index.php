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

       class TaskControl extends React.Component {
            state = {
                isRunning: false,
                elapsed: 0,
                interval: null,
                name: 'Task name',
            }

            addSecond() {
                this.setState({
                    elapsed: this.state.elapsed + 1
                });
            }

            startTime() {
                const interval = setInterval(() => this.addSecond(), 1000);
                this.setState({
                    isRunning: true,
                    interval
                });
            }
            stopTime() {
                clearInterval(this.state.interval);
                this.setState({ 
                    isRunning: false,
                    interval: null
                });
            }
            resetTime() {
                clearInterval(this.state.interval);
                this.setState({
                    isRunning: false,
                    interval: null,
                    elapsed: 0
                });
            }

            async handleSubmit( event ) {
                event.preventDefault();
                console.log(this.state);

                const { isRunning, name, elapsed } = this.state;
                const date = moment().unix();

                let formData = new FormData();
                formData.append('name', name);
                formData.append('status', !isRunning);
                formData.append('elapsed', elapsed);
                formData.append('date', date);

                try {
                    const response = await axios({
                        method: 'POST',
                        url: '/backend/tasks.php',
                        data: formData,
                        config: { headers: {'Content-Type': 'multipart/form-data' }}
                    });

                    console.log('response', response);
                    
                    if (response.status !== 200) {
                        alert("Error adding the new task.");
                        return false;
                    }

                    if (isRunning) {
                        this.stopTime();
                    } else {
                        this.startTime();
                        if (!response.data.exist) {
                            // ADD
                            this.props.addTask({
                                id: response.data.id,
                                name: response.data.name,
                                elapsed: 0,
                                created_date: date
                            })
                        } else {
                            // UPDATE
                        }
                    }
                    // alert(`${response.data['name']} was added.`);

                } catch (e) {
                    console.log('error', e);
                    alert("Error adding the new task.");
                }
            }

            render(){
                return (
                    <div>
                        <h2>{this.state.name}: { moment.duration(this.state.elapsed, 'seconds').format("H:mm:ss") }</h2>
                        <form>
                            <input style={{ width: "250px", height: "40px" }} type="text" name="name" value={this.state.name} placeholder="Name of the task" required
                                disabled={this.state.isRunning}
                                onChange={e => this.setState({ name: e.target.value })}
                                onFocus={(e) => e.target.select()}/>
                            <button style={{ width: "160px", height: "50px", borderRadius: '5px', backgroundColor: this.state.isRunning ? "red" : "green", "color": "white", "fontWeight": "bold" }} type="submit" onClick={e => this.handleSubmit(e)}>
                                {this.state.isRunning ? "STOP" : "START"}
                            </button>
                        </form>
                    </div>
                );
            }
        }

        class App extends React.Component {
            state = {
                tasks: []
            }

            componentDidMount() {
                const url = '/backend/tasks.php'
                axios.get(url).then(response => response.data)
                    .then((data) => {
                        this.setState({ tasks: data });
                        console.log(this.state.tasks);
                    });
            }

            addTaskHandler(task) {
                let tasks = this.state.tasks;
                tasks.unshift(task);
                this.setState({ tasks: tasks });
            }

            render() {
                return (
                    <React.Fragment>
                    <div>
                        <h1>Time Tracker</h1>
                        <h4>Control the time you invest in each daily tasks</h4>
                    </div>
                    <div>
                        <TaskControl addTask={(task) => this.addTaskHandler(task)} />
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
                                {this.state.tasks.map((task) => (
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