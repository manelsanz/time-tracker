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

<body style="font-family: arial;">

    <div id='root'></div>

    <script type="text/babel">

       class TaskControl extends React.Component {
            state = {
                isRunning: false,
                elapsed: 0,
                interval: null,
                name: '',
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
                    elapsed: 0,
                    name: ''
                });
            }

            async handleSubmit( event ) {
                event.preventDefault();
                // console.log(this.state);
                const { currentTask } = this.props;
                const { isRunning, name, elapsed } = this.state;
                const date = moment().unix();

                let formData = new FormData();
                formData.append('name', name);
                formData.append('status', isRunning ? 0 : 1);
                formData.append('date', date);

                let elapsed_aux = elapsed;

                if (isRunning) {
                    if (currentTask && currentTask.name == name) {
                        elapsed_aux = elapsed - currentTask.elapsed;   
                    }
                    formData.append('id', currentTask.id);
                    formData.append('elapsed', elapsed_aux);
                }

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

                    const task = {
                        id: response.data.id,
                        name: response.data.name,
                        elapsed: response.data.elapsed,
                        created_date: response.data.created_date
                    };

                    if (isRunning) {
                        this.stopTime();
                        this.props.updateTask(task);

                    } else {
                        if (currentTask && currentTask.name != name) {
                            console.log('Reset time');
                            this.resetTime();
                        }
                        this.startTime();
                        if (!response.data.exist) {
                            this.props.addTask(task)
                        }
                    }

                } catch (e) {
                    console.log('error', e);
                    alert("Error adding the new task.");
                }
            }

            render() {
                const { name, elapsed, isRunning } = this.state;
                return (
                    <div>
                        <h2>
                            {name || 'Task name'} - { moment.duration(elapsed, 'seconds').format("H:mm:ss") }
                            {elapsed < 60 ? ' seconds' : (elapsed < 3600 ? ' minutes' : ' hours')}
                        </h2>
                        <form>
                            <input style={{ width: "300px", height: "43px", borderRadius: '5px' }} type="text" name="name" value={name} placeholder="Name of the task" required="true"
                                disabled={isRunning}
                                onChange={e => this.setState({ name: e.target.value })}
                                onFocus={(e) => e.target.select()}/>
                            <button style={{ width: "160px", height: "50px", borderRadius: '5px', backgroundColor: isRunning ? "red" : "green", "color": "white", "fontWeight": "bold" }} type="submit" onClick={e => this.handleSubmit(e)}>
                                {isRunning ? "STOP" : "START"}
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
                this.setState({ tasks });
            }

            updateTaskHandler(task) {
                console.log('task a update', task);
                this.setState(prevState => {
                    const tasks = prevState.tasks.map((item) => {
                        if (item.id == task.id) {
                            return task;
                        } else {
                            return item;
                        }
                    });
                    return { tasks };
                });
            }

            render() {
                return (
                    <React.Fragment>
                    <div>
                        <h1 style={{ color: "blue" }}>Time Tracker</h1>
                        <h4>Know the time you invest in tasks during the day</h4>
                    </div>
                    <div>
                        <TaskControl 
                            addTask={(task) => this.addTaskHandler(task)} 
                            updateTask={(task) => this.updateTaskHandler(task)} 
                            currentTask={this.state.tasks[0]} 
                        />
                        <div>
                            <h2>Tasks</h2>
                            <table border="true" style={{ border: '2px', borderColor: 'blue', borderCollapse: 'collapse', borderSpacing: 0, borderRadius: '5px', width: '100%' }}>
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
                                    <td style={{ textAlign: 'center' }}>{ moment.duration(parseInt(task.elapsed), 'seconds').format("H:mm:ss") }</td>
                                    <td style={{ textAlign: 'center' }}>{ moment.unix(parseInt(task.created_date)).format("LLL") }</td>
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