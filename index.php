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
                currentTask: null,
                lastTask: null
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
                });
            }

            async handleSubmit( event ) {
                event.preventDefault();
                this.props.setLoading(true);

                const { isRunning, name, elapsed, currentTask, lastTask } = this.state;
                const date = moment().unix();

                if (!name) {
                    alert("The name can't be empty!");
                    return false;
                }

                let formData = new FormData();
                formData.append('name', name);
                formData.append('status', isRunning ? 0 : 1);
                formData.append('date', date);

                let elapsed_aux = elapsed;

                if (isRunning) {
                    if (lastTask && (currentTask.id == lastTask.id)) {
                        console.log('Minus');
                        elapsed_aux = elapsed - lastTask.elapsed;   
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

                    if (response.status !== 200) {
                        alert("Error adding the new task.");
                        return false;
                    }

                    let task = {
                        id: response.data.id,
                        name: response.data.name,
                        elapsed: response.data.elapsed,
                        created_date: response.data.created_date
                    };

                    if (isRunning) {
                        this.stopTime();
                        this.props.updateTask(task);
                        this.setState({
                            lastTask: task
                        });

                    } else {
                        if (!response.data.exist) {
                            this.props.addTask(task)
                        }
                        if (lastTask && lastTask.name != name) {
                            this.resetTime();
                        } 
                        // else {
                        //     task.elapsed = elapsed_aux;
                        // }
                        this.setState({
                            currentTask: task
                        });
                        this.startTime();

                    }
                    this.props.setLoading(false);

                } catch (e) {
                    console.log('error', e);
                    alert("Error adding the new task.");
                }
            }

            render() {
                const { name, elapsed, isRunning } = this.state;
                return (
                    <div style={{ textAlign: 'center' }}>
                        <h2>
                            {name || 'Task name'} - { moment.duration(elapsed, 'seconds').format("H:mm:ss") }
                            {elapsed < 60 ? ' seconds' : (elapsed < 3600 ? ' minutes' : ' hours')}
                        </h2>
                        <form>
                            <input style={{ width: "280px", height: "43px", borderRadius: '5px' }} type="text" name="name" value={name} placeholder="Name of the task" required={true}
                                disabled={isRunning}
                                onChange={e => this.setState({ name: e.target.value })}
                                onFocus={(e) => e.target.select()}/>
                            <button style={{ width: "280px", height: "50px", borderRadius: '5px', backgroundColor: isRunning ? "red" : "green", "color": "white", "fontWeight": "bold" }} type="submit" onClick={e => this.handleSubmit(e)}>
                                {isRunning ? "STOP" : "START"}
                            </button>
                        </form>
                    </div>
                );
            }
        }

        class App extends React.Component {
            state = {
                tasks: [],
                loading: false
            }

            setLoadingHandler(status) {
                this.setState({
                    loading: status
                });
            }

            componentDidMount() {
                this.props.setLoading(true);

                const url = '/backend/tasks.php'
                axios.get(url).then(response => response.data)
                    .then((data) => {
                        this.setState({ tasks: data });
                        this.props.setLoading(false);
                    });
            }

            addTaskHandler(task) {
                let tasks = this.state.tasks;
                tasks.unshift(task);
                this.setState({ tasks });
            }

            updateTaskHandler(task) {
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
                const { tasks } = this.state;
                // const reduceElapsed = (acc, task) => acc + task.elapsed;
                const totalElapsed = tasks.reduce((acc, task) => acc + task.elapsed); 

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
                            setLoading={() => this.setLoadingHandler()}
                        />
                        <div>
                            <h2>Summary of Tasks - {totalElapsed} {totalElapsed < 60 ? ' seconds' : (totalElapsed < 3600 ? ' minutes' : ' hours')} in total </h2>
                            <table border="true" style={{ border: '2px', borderColor: 'blue', borderCollapse: 'collapse', borderSpacing: 0, borderRadius: '5px', width: '100%' }}>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Elapsed</th>
                                    <th>Created date</th>
                                </tr>
                            </thead>

                            <tbody>
                                {tasks.length === 0 && 
                                    (<tr><td colSpan={4} style={{ textAlign: 'center', padding: '20px' }}>There aren't tasks yet.</td></tr>)}

                                {tasks.map((task) => (
                                <tr key={`task-${task.id}`}>
                                    <td style={{ textAlign: 'center', padding: '15px' }}>{ task.id }</td>
                                    <td style={{ textAlign: 'center', padding: '15px' }}>{ task.name }</td>
                                    <td style={{ textAlign: 'center', padding: '15px' }}>{ moment.duration(parseInt(task.elapsed), 'seconds').format("H:mm:ss")} {task.elapsed < 60 ? ' seconds' : (task.elapsed < 3600 ? ' minutes' : ' hours')}</td>
                                    <td style={{ textAlign: 'center', padding: '15px' }}>{ moment.unix(parseInt(task.created_date)).format("LLL") }</td>
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